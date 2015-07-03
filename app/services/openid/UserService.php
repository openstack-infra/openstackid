<?php
namespace services\openid;

use auth\IUserRepository;
use auth\User;
use Exception;
use openid\model\IOpenIdUser;
use openid\services\IUserService;
use utils\db\ITransactionService;
use utils\services\ILogService;

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
     * @param IUserRepository $repository
     * @param ITransactionService $tx_service
     * @param ILogService $log_service
     */
    public function __construct(IUserRepository $repository, ITransactionService $tx_service, ILogService $log_service)
    {
        $this->repository = $repository;
        $this->log_service = $log_service;
        $this->tx_service = $tx_service;
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

                Log::warning(sprintf("User %d locked ", $identifier));
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
    public function unlockUser($identifier)
    {
        try {
            $user = $this->repository->get($identifier);
            if (!is_null($user)) {

                $user->lock = false;
                $this->repository->update($user);

                Log::warning(sprintf("User %d unlocked ", $identifier));
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
        $repository = $this->repository;
        return $this->tx_service->transaction(function () use($member, $repository){
            //create user
            $user                       = new User();
            $user->external_identifier  = $member->ID;
            $user->identifier           = $member->ID;
            $user->last_login_date      = gmdate("Y-m-d H:i:s", time());
            $user->active               = true;
            $user->lock                 = false;
            $user->login_failed_attempt = 0;

            $repository->add($user);
            $proposed_username     = strtolower($member->FirstName . "." . $member->Surname);
            $done                  = false;
            $fragment_nbr          = 1;
            $aux_proposed_username = $proposed_username;
            do
            {

                $old_user = $repository->getOneByCriteria
                (
                    array
                    (
                        array('name' => 'identifier', 'op' => '=', 'value' => $aux_proposed_username),
                        array('name' => 'id', 'op' => '<>', 'value' => $user->id)
                    )
                );

                if (is_null($old_user))
                {

                    $user->identifier = $aux_proposed_username;
                    $done = $repository->update($user);
                }
                else
                {
                    $aux_proposed_username = $proposed_username . "." . $fragment_nbr;
                    $fragment_nbr++;
                }

            } while (!$done);

            return $user;
        });
    }

}