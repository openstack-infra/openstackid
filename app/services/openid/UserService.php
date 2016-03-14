<?php

namespace services\openid;

use auth\IUserNameGeneratorService;
use auth\IUserRepository;
use auth\User;
use Exception;
use openid\model\IOpenIdUser;
use openid\services\IUserService;
use utils\db\ITransactionService;
use utils\services\ILogService;
use Illuminate\Support\Facades\Mail;
use utils\services\IServerConfigurationService;

/**
 * Class UserService
 * @package services\openid
 */
final class UserService implements IUserService
{

     /**
     * @var IUserRepository
     */
    private $repository;
    /**
     * @var ILogService
     */
    private $log_service;
    /**
     * @var ITransactionService
     */
    private $tx_service;

    /**
     * @var IUserNameGeneratorService
     */
    private $user_name_generator;

    /**
     * @var IServerConfigurationService
     */
    private $configuration_service;

    /**
     * UserService constructor.
     * @param IUserRepository $repository
     * @param IUserNameGeneratorService $user_name_generator
     * @param ITransactionService $tx_service
     * @param IServerConfigurationService $configuration_service
     * @param ILogService $log_service
     */
    public function __construct
    (
        IUserRepository $repository,
        IUserNameGeneratorService $user_name_generator,
        ITransactionService $tx_service,
        IServerConfigurationService $configuration_service,
        ILogService $log_service
    )
    {
        $this->repository            = $repository;
        $this->user_name_generator   = $user_name_generator;
        $this->configuration_service = $configuration_service;
        $this->log_service           = $log_service;
        $this->tx_service            = $tx_service;
    }


    /**
     * @param $identifier
     * @return mixed|void
     * @throws \Exception
     */
    public function updateLastLoginDate($identifier)
    {
        try {
            $user = $this->repository->get($identifier);
            if (!is_null($user)) {
                $user->last_login_date = gmdate("Y-m-d H:i:s", time());
                $this->repository->update($user);
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            throw $ex;
        }
    }

    /**
     * @param $identifier
     * @return mixed|void
     * @throws \Exception
     */
    public function updateFailedLoginAttempts($identifier)
    {
        try {
            $user = $this->repository->get($identifier);
            if (!is_null($user)) {
                $user->login_failed_attempt += 1;
                $this->repository->update($user);
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            throw $ex;
        }
    }

    /**
     * @param $identifier
     * @return mixed|void
     * @throws \Exception
     */
    public function lockUser($identifier)
    {
        try {
            $user = $this->repository->get($identifier);
            if (!is_null($user)) {

                $user->lock = true;
                $this->repository->update($user);
                $support_email = $this->configuration_service->getConfigValue('SupportEmail');
                Mail::send('emails.auth.user_locked', array
                (
                    'user_name'     => $user->getFullName(),
                    'attempts'      => $user->login_failed_attempt,
                    'support_email' => $support_email,
                ), function($message) use ($user, $support_email)
                {
                    $message
                            ->from($support_email, 'OpenStack Support Team')
                            ->to($user->getEmail(), $user->getFullName())
                            ->subject('OpenStackId - your user has been locked!');
                });
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            throw $ex;
        }
    }

    /**
     * @param $identifier
     * @return void
     * @throws \Exception
     */
    public function unlockUser($identifier)
    {
        try {
            $user = $this->repository->get($identifier);
            if (!is_null($user)) {

                $user->lock = false;
                $this->repository->update($user);
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            throw $ex;
        }
    }

    /**
     * @param $identifier
     * @return mixed|void
     * @throws \Exception
     */
    public function activateUser($identifier)
    {
        try {
            $user = $this->repository->get($identifier);
            if (!is_null($user)) {
                $user->active = true;
                $this->repository->update($user);
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            throw $ex;
        }
    }

    /**
     * @param $identifier
     * @return mixed|void
     * @throws \Exception
     */
    public function deActivateUser($identifier)
    {
        try {
            $user = $this->repository->get($identifier);
            if (!is_null($user)) {
                $user->active = false;
                $this->repository->update($user);
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            throw $ex;
        }
    }

    /**
     * @param $identifier
     * @param $show_pic
     * @param $show_full_name
     * @param $show_email
     * @return bool
     * @throws \Exception
     */
    public function saveProfileInfo($identifier, $show_pic, $show_full_name, $show_email)
    {
        try {
            $user = $this->repository->get($identifier);
            if (!is_null($user)) {
                $user->public_profile_show_photo = $show_pic;
                $user->public_profile_show_fullname = $show_full_name;
                $user->public_profile_show_email = $show_email;

                return $this->repository->update($user);
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            throw $ex;
        }

        return false;
    }

    public function get($id)
    {
        return $this->repository->get($id);
    }

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr = 1, $page_size = 10, array $filters = array(), array $fields = array('*'))
    {
        return $this->repository->getAll($page_nbr, $page_size, $filters, $fields);
    }

    /**
     * @param \Member $member
     * @return IOpenIdUser
     */
    public function buildUser(\Member $member)
    {
        $repository          = $this->repository;
        $user_name_generator = $this->user_name_generator;

        return $this->tx_service->transaction(function () use($member, $user_name_generator, $repository){
            //create user
            $old_user = $repository->getByExternalId($member->ID);
            if(!is_null($old_user))
                throw new ValidationException(sprintf('already exists an user with external_identifier %s', $member->ID));

            $user                       = new User();
            $user->external_identifier  = $member->ID;
            $user->identifier           = $member->ID;
            $user->last_login_date      = gmdate("Y-m-d H:i:s", time());
            $user->active               = true;
            $user->lock                 = false;
            $user->login_failed_attempt = 0;

            $done                  = false;
            $fragment_nbr          = 1;
            $identifier            = $original_identifier = $user_name_generator->generate($member);
            do
            {
                $old_user = $repository->getByIdentifier($identifier);
                if(!is_null($old_user))
                {
                    $identifier = $original_identifier . IUserNameGeneratorService::USER_NAME_CHAR_CONNECTOR . $fragment_nbr;
                    $fragment_nbr++;
                    continue;
                }
                $user->identifier = $identifier;
                break;
            } while (1);
            $repository->add($user);
            return $user;
        });
    }

}