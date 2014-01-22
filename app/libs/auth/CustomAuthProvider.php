<?php


namespace auth;
use auth\exceptions\AuthenticationException;
use auth\exceptions\AuthenticationInvalidPasswordAttemptException;
use auth\exceptions\AuthenticationLockedUserLoginAttempt;
use Exception;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;
use Log;
use Member;
use openid\services\OpenIdServiceCatalog;
use utils\services\Registry;
use utils\services\UtilsServiceCatalog;
use DB;

/**
 * Class CustomAuthProvider
 * Custom Authentication Provider against SS DB
 * @package auth
 */
class CustomAuthProvider implements UserProviderInterface
{

    private $auth_extension_service;

    public function __construct(IAuthenticationExtensionService $auth_extension_service){
        $this->auth_extension_service = $auth_extension_service;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function retrieveById($identifier)
    {
        try {
            //here we do the manuel join between 2 DB, (openid and SS db)
            $user   = User::where('external_id', '=', $identifier)->first();
            $member = Member::where('Email', '=', $identifier)->first();
            if (!is_null($member) && !is_null($user)) {
                $user->setMember($member);
                return $user;
            }
            return null;
        } catch (Exception $ex) {
            Log::error($ex);
            return null;
        }
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $user = null;
        try {
            DB::transaction(function () use ($credentials, &$user) {

                if (!isset($credentials['username']) || !isset($credentials['password']))
                    throw new AuthenticationException("invalid crendentials");

                $identifier = $credentials['username'];
                $password   = $credentials['password'];
                $user       = User::where('external_id', '=', $identifier)->first();

                //check user status...
                if (!is_null($user) && ($user->lock || !$user->active)){
                    Log::warning(sprintf("user %s is on lock state",$identifier));
                    throw new AuthenticationLockedUserLoginAttempt($identifier,sprintf("user %s is on lock state",$identifier));
                }

                //get SS member
                $member = Member::where('Email', '=', $identifier)->first();
                if (is_null($member)) //member must exists
                    throw new AuthenticationException(sprintf("member %s does not exists!", $identifier));

                $valid_password = $member->checkPassword($password);

                if(!$valid_password)
                    throw new AuthenticationInvalidPasswordAttemptException($identifier,sprintf("invalid login attempt for user %s ",$identifier));

                //if user does not exists, then create it
                if (is_null($user)) {
                    //create user
                    $user = new User();
                    $user->external_id     = $member->Email;
                    $user->identifier      = $member->Email;
                    $user->last_login_date = gmdate("Y-m-d H:i:s", time());
                    $user->Save();
                    $user = User::where('external_id', '=', $identifier)->first();
                }

                $user_service = Registry::getInstance()->get(OpenIdServiceCatalog::UserService);

                $user_name = $member->FirstName . "." . $member->Surname;
                //do association between user and member
                $user_service->associateUser($user->id, strtolower($user_name));

                $server_configuration = Registry::getInstance()->get(UtilsServiceCatalog::ServerConfigurationService);

                //update user fields
                $user->last_login_date      = gmdate("Y-m-d H:i:s", time());
                $user->login_failed_attempt = 0;
                $user->active               = true;
                $user->lock                 = false;
                $user->Save();

                //reload user...
                $user                       = User::where('external_id', '=', $identifier)->first();
                $user->setMember($member);

                $auth_extensions = $this->auth_extension_service->getExtensions();

                foreach($auth_extensions as $auth_extension){
                    $auth_extension->process($user);
                }
            });
         } catch (Exception $ex) {
            $checkpoint_service = Registry::getInstance()->get(UtilsServiceCatalog::CheckPointService);
            $checkpoint_service->trackException($ex);
            Log::error($ex);
            $user = null;
        }
        return $user;
    }


    /**
     * Validate a user against the given credentials.
     * @param UserInterface $user
     * @param array $credentials
     * @return bool
     * @throws exceptions\AuthenticationException
     */
    public function validateCredentials(UserInterface $user, array $credentials)
    {
        if (!isset($credentials['username']) || !isset($credentials['password']))
            throw new AuthenticationException("invalid crendentials");

        try {
            $identifier = $credentials['username'];
            $password   = $credentials['password'];
            $user       = User::where('external_id', '=', $identifier)->first();

            if (is_null($user) || $user->lock || !$user->active)
                return false;

            $member = Member::where('Email', '=', $identifier)->first();

            return !is_null($member) ? $member->checkPassword($password) : false;
        } catch (Exception $ex) {
            Log::error($ex);
            return false;
        }
    }
}