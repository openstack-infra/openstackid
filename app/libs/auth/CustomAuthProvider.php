<?php


namespace auth;
use auth\exceptions\AuthenticationException;
use Exception;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;
use Log;
use Member;
use openid\helpers\OpenIdErrorMessages;
use openid\requests\OpenIdAuthenticationRequest;
use openid\services\Registry;
use openid\services\ServiceCatalog;
use  auth\exceptions\AuthenticationInvalidPasswordAttemptException;

class CustomAuthProvider implements UserProviderInterface
{


    public function __construct(){

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
            $user = OpenIdUser::where('external_id', '=', $identifier)->first();
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

        try {

            if (!isset($credentials['username']) || !isset($credentials['password']))
                throw new AuthenticationException("invalid crendentials");

            $identifier = $credentials['username'];
            $password = $credentials['password'];
            $user = OpenIdUser::where('external_id', '=', $identifier)->first();

            //check user status...
            if (!is_null($user) && ($user->lock || !$user->active)){
                Log::warning(sprintf("user %s is on lock state",$identifier));
                return null;
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
                $user = new OpenIdUser();
                $user->external_id = $member->Email;
                $user->identifier  = $member->Email;
                $user->last_login_date = gmdate("Y-m-d H:i:s", time());
                $user->Save();
                $user = OpenIdUser::where('external_id', '=', $identifier)->first();
            }

            $user_service = Registry::getInstance()->get(ServiceCatalog::UserService);

            $user_name = $member->FirstName . "." . $member->Surname;
            //do association between user and member
            $user_service->associateUser($user->id, strtolower($user_name));

            $server_configuration = Registry::getInstance()->get(ServiceCatalog::ServerConfigurationService);

            //update user fields
            $user->last_login_date = gmdate("Y-m-d H:i:s", time());
            $user->login_failed_attempt = 0;
            $user->active = true;
            $user->lock = false;
            $user->Save();

            //reload user...
            $user = OpenIdUser::where('external_id', '=', $identifier)->first();
            $user->setMember($member);

            //check if we have a current openid message
            $memento_service = Registry::getInstance()->get(ServiceCatalog::MementoService);
            $msg = $memento_service->getCurrentRequest();
            if (is_null($msg) || !$msg->isValid() || !OpenIdAuthenticationRequest::IsOpenIdAuthenticationRequest($msg))
                return $user;
            else {
                //check if current user is has the same identity that the one claimed on openid message
                $auth_request = new OpenIdAuthenticationRequest($msg);
                if ($auth_request->isIdentitySelectByOP())
                    return $user;
                $claimed_id = $auth_request->getClaimedId();
                $identity = $auth_request->getIdentity();
                $current_identity = $server_configuration->getUserIdentityEndpointURL($user->getIdentifier());
                if ($claimed_id == $current_identity || $identity == $current_identity)
                    return $user;
                Log::warning(sprintf(OpenIdErrorMessages::AlreadyExistSessionMessage, $current_identity, $identity));
                //if not return fail ( we cant log in with a different user that the one stated on the authentication message!
                return null;
            }

        } catch (Exception $ex) {
            $checkpoint_service = Registry::getInstance()->get(ServiceCatalog::CheckPointService);
            $checkpoint_service->trackException($ex);
            Log::error($ex);
            return null;
        }
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Auth\UserInterface $user
     * @param  array $credentials
     * @return bool
     */
    public function validateCredentials(UserInterface $user, array $credentials)
    {
        if (!isset($credentials['username']) || !isset($credentials['password']))
            throw new AuthenticationException("invalid crendentials");

        try {
            $identifier = $credentials['username'];
            $password = $credentials['password'];
            $user = OpenIdUser::where('external_id', '=', $identifier)->first();

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