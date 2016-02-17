<?php
namespace auth;

use auth\exceptions\AuthenticationException;
use auth\exceptions\AuthenticationInvalidPasswordAttemptException;
use auth\exceptions\AuthenticationLockedUserLoginAttempt;
use Exception;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;
use openid\services\IUserService;
use utils\db\ITransactionService;
use utils\services\ICheckPointService;
use utils\services\ILogService;

/**
 * Class CustomAuthProvider
 * Custom Authentication Provider against SS DB
 * @package auth
 */
class CustomAuthProvider implements UserProviderInterface
{

    /**
     * @var IAuthenticationExtensionService
     */
    private $auth_extension_service;
    /**
     * @var IUserService
     */
    private $user_service;
    /**
     * @var ICheckPointService
     */
    private $checkpoint_service;
    /**
     * @var IUserService
     */
    private $user_repository;
    /**
     * @var IMemberRepository
     */
    private $member_repository;
    /**
     * @var ITransactionService
     */
    private $tx_service;
    /**
     * @var ILogService
     */
    private $log_service;

    /**
     * @param IUserRepository $user_repository
     * @param IMemberRepository $member_repository
     * @param IAuthenticationExtensionService $auth_extension_service
     * @param IUserService $user_service
     * @param ICheckPointService $checkpoint_service
     * @param ITransactionService $tx_service
     * @param ILogService $log_service
     */
    public function __construct(
        IUserRepository $user_repository,
        IMemberRepository $member_repository,
        IAuthenticationExtensionService $auth_extension_service,
        IUserService $user_service,
        ICheckPointService $checkpoint_service,
        ITransactionService $tx_service,
        ILogService $log_service
    ) {

        $this->auth_extension_service = $auth_extension_service;
        $this->user_service = $user_service;
        $this->checkpoint_service = $checkpoint_service;
        $this->user_repository = $user_repository;
        $this->member_repository = $member_repository;
        $this->tx_service = $tx_service;
        $this->log_service = $log_service;
    }

    /**
     * Retrieve a user by their unique identifier.
     * @param  mixed $identifier
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function retrieveById($identifier)
    {
        try {
            //here we do the manuel join between 2 DB, (openid and SS db)
            $user   = $this->user_repository->getByExternalId($identifier);
            $member = $this->member_repository->get($identifier);
            if (!is_null($member) && $member->canLogin() && !is_null($user)) {
                $user->setMember($member);
                return $user;
            }

        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return null;
        }

        return null;
    }

    /**
     * Retrieve a user by the given credentials.
     * @param  array $credentials
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $user_service = $this->user_service;
        $auth_extension_service = $this->auth_extension_service;
        $user_repository = $this->user_repository;
        $member_repository = $this->member_repository;
        $log_service = $this->log_service;
        $checkpoint_service = $this->checkpoint_service;


        return $this->tx_service->transaction(function () use (
            $credentials,
            $user_repository,
            $member_repository,
            $user_service,
            $auth_extension_service,
            $log_service,
            $checkpoint_service
        ) {

            $user = null;

            try
            {

                if (!isset($credentials['username']) || !isset($credentials['password']))
                {
                    throw new AuthenticationException("invalid crendentials");
                }

                $email    = $credentials['username'];
                $password = $credentials['password'];

                //get SS member

                $member = $member_repository->getByEmail($email);

                if (is_null($member)) //member must exists
                {
                    throw new AuthenticationException(sprintf("member %s does not exists!", $email));
                }

                if(!$member->canLogin())
                {
                    throw new AuthenticationException(sprintf("member %s does not exists!", $email));
                }

                $valid_password = $member->checkPassword($password);

                if (!$valid_password)
                {
                    throw new AuthenticationInvalidPasswordAttemptException($member->ID,
                        sprintf("invalid login attempt for user %s ", $email));
                }


                $user = $user_repository->getByExternalId($member->ID);

                if (!$user) {
                    $user = $user_service->buildUser($member);
                }

                //check user status...
                if ($user->lock || !$user->active) {
                    Log::warning(sprintf("user %s is on lock state", $email));
                    throw new AuthenticationLockedUserLoginAttempt($email,
                        sprintf("user %s is on lock state", $email));
                }

                //update user fields
                $user->last_login_date = gmdate("Y-m-d H:i:s", time());
                $user->login_failed_attempt = 0;
                $user->active = true;
                $user->lock = false;
                $user_repository->update($user);
                $user->setMember($member);

                $auth_extensions = $auth_extension_service->getExtensions();

                foreach ($auth_extensions as $auth_extension)
                {
                    $auth_extension->process($user);
                }


            }
            catch (Exception $ex)
            {
                $checkpoint_service->trackException($ex);
                $log_service->error($ex);
                $user = null;
            }

            return $user;
        });

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
        if (!isset($credentials['username']) || !isset($credentials['password'])) {
            throw new AuthenticationException("invalid crendentials");
        }
        try {
            $email = $credentials['username'];
            $password = $credentials['password'];

            $member = $this->member_repository->getByEmail($email);

            if (!$member || !$member->canLogin() || !$member->checkPassword($password)) {
                return false;
            }

            $user = $this->user_repository->getByExternalId($member->ID);

            if (is_null($user) || $user->lock || !$user->active) {
                return false;
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return false;
        }

        return true;
    }

    /**
     * Retrieve a user by by their unique identifier and "remember me" token.
     * @param  mixed $identifier
     * @param  string $token
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function retrieveByToken($identifier, $token)
    {
        return $this->user_repository->getByToken($identifier, $token);
    }

    /**
     * Update the "remember me" token for the given user in storage.
     * @param  \Illuminate\Auth\UserInterface $user
     * @param  string $token
     * @return void
     */
    public function updateRememberToken(UserInterface $user, $token)
    {
        $user->setAttribute($user->getRememberTokenName(), $token);

        $user->save();
    }
}