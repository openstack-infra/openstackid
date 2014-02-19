<?php
namespace auth;

use auth\exceptions\AuthenticationException;
use auth\exceptions\AuthenticationInvalidPasswordAttemptException;
use auth\exceptions\AuthenticationLockedUserLoginAttempt;
use Exception;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;
use openid\services\IUserService;
use utils\services\ICheckPointService;
use utils\db\ITransactionService;
use utils\services\ILogService;

/**
 * Class CustomAuthProvider
 * Custom Authentication Provider against SS DB
 * @package auth
 */
class CustomAuthProvider implements UserProviderInterface
{

    private $auth_extension_service;
    private $user_service;
    private $checkpoint_service;
	private $user_repository;
	private $member_repository;
	private $tx_service;
	private $log_service;

    public function __construct(IUserRepository $user_repository,
	                            IMemberRepository $member_repository,
	                            IAuthenticationExtensionService $auth_extension_service,
                                IUserService $user_service,
                                ICheckPointService $checkpoint_service,
                                ITransactionService $tx_service,
								ILogService         $log_service){

        $this->auth_extension_service = $auth_extension_service;
        $this->user_service           = $user_service;
        $this->checkpoint_service     = $checkpoint_service;
	    $this->user_repository        = $user_repository;
	    $this->member_repository      = $member_repository;
	    $this->tx_service             = $tx_service;
	    $this->log_service            = $log_service;
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
            $user   = $this->user_repository->getByExternalId($identifier);
            $member = $this->member_repository->getByEmail($identifier);
            if (!is_null($member) && !is_null($user)) {
                $user->setMember($member);
                return $user;
            }
            return null;
        } catch (Exception $ex) {
            $this->log_service->error($ex);
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
        $user                   = null;
	    $user_service           = $this->user_service;
	    $auth_extension_service = $this->auth_extension_service;
	    $user_repository        = $this->user_repository;
	    $member_repository      = $this->member_repository;

        try {


            $this->tx_service->transaction(function () use ($credentials, &$user,&$user_repository,&$member_repository, &$user_service,&$auth_extension_service) {

                if (!isset($credentials['username']) || !isset($credentials['password']))
                    throw new AuthenticationException("invalid crendentials");

                $identifier = $credentials['username'];
                $password   = $credentials['password'];
                $user       = $user_repository->getByExternalId($identifier);

                //check user status...
                if (!is_null($user) && ($user->lock || !$user->active)){
                    Log::warning(sprintf("user %s is on lock state",$identifier));
                    throw new AuthenticationLockedUserLoginAttempt($identifier,sprintf("user %s is on lock state",$identifier));
                }

                //get SS member

	            $member = $member_repository->getByEmail($identifier);

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
	                $user_repository->add($user);
                }

                $user_name = $member->FirstName . "." . $member->Surname;
                //do association between user and member
	            $user_service->associateUser($user, strtolower($user_name));

                //update user fields
                $user->last_login_date      = gmdate("Y-m-d H:i:s", time());
                $user->login_failed_attempt = 0;
                $user->active               = true;
                $user->lock                 = false;
	            $user_repository->update($user);
                //reload user...
                //$user                        = $user_repository->getByExternalId($identifier);
                $user->setMember($member);

                $auth_extensions = $auth_extension_service->getExtensions();

                foreach($auth_extensions as $auth_extension){
                    $auth_extension->process($user);
                }
            });
         } catch (Exception $ex) {
            $this->checkpoint_service->trackException($ex);
	        $this->log_service->error($ex);
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
            $user       = $this->user_repository->getByExternalId($identifier);
            if (is_null($user) || $user->lock || !$user->active)
                return false;
            $member = $this->member_repository->getByEmail($identifier);
            return !is_null($member) ? $member->checkPassword($password) : false;
        } catch (Exception $ex) {
	        $this->log_service->error($ex);
            return false;
        }
    }
}