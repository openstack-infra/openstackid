<?php namespace Services\OpenId;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

use Auth\IUserNameGeneratorService;
use Auth\Repositories\IUserRepository;
use Auth\User;
use Models\Member;
use OpenId\Models\IOpenIdUser;
use OpenId\Services\IUserService;
use Services\Exceptions\ValidationException;
use Utils\Db\ITransactionService;
use Utils\Exceptions\EntityNotFoundException;
use Utils\Services\ILogService;
use Illuminate\Support\Facades\Mail;
use Utils\Services\IServerConfigurationService;

/**
 * Class UserService
 * @package Services\OpenId
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
     * @param int $user_id
     * @return void
     * @throws EntityNotFoundException
     */
    public function updateLastLoginDate($user_id)
    {
        $this->tx_service->transaction(function() use($user_id){
            $user = $this->repository->get($user_id);
            if(is_null($user)) throw new EntityNotFoundException();
            $user->last_login_date = gmdate("Y-m-d H:i:s", time());
            $this->repository->add($user);
        });
    }

    /**
     * @param int $user_id
     * @return void
     * @throws EntityNotFoundException
     */
    public function updateFailedLoginAttempts($user_id)
    {
         $this->tx_service->transaction(function() use($user_id){
            $user = $this->repository->get($user_id);
            if(is_null($user)) throw new EntityNotFoundException();
            $user->login_failed_attempt += 1;
            $this->repository->add($user);
        });
    }

    /**
     * @param int $user_id
     * @return void
     * @throws EntityNotFoundException
     */
    public function lockUser($user_id)
    {
        $this->tx_service->transaction(function() use($user_id){
            $user = $this->repository->get($user_id);
            if(is_null($user)) throw new EntityNotFoundException();

            $user->lock = true;
            $this->repository->add($user);

            $support_email = $this->configuration_service->getConfigValue('SupportEmail');
            Mail::send('emails.auth.user_locked', [
                'user_name'     => $user->getFullName(),
                'attempts'      => $user->login_failed_attempt,
                'support_email' => $support_email,
            ], function($message) use ($user, $support_email)
            {
                $message
                    ->from($support_email, 'OpenStack Support Team')
                    ->to($user->getEmail(), $user->getFullName())
                    ->subject('OpenStackId - your user has been locked!');
            });
        });
    }

    /**
     * @param int $user_id
     * @return void
     * @throws EntityNotFoundException
     */
    public function unlockUser($user_id)
    {
        $this->tx_service->transaction(function() use($user_id){
            $user = $this->repository->get($user_id);
            if(is_null($user)) throw new EntityNotFoundException();
            $user->lock = false;
            $this->repository->update($user);
        });
    }

    /**
     * @param int $user_id
     * @return void
     * @throws EntityNotFoundException
     */
    public function activateUser($user_id)
    {
        $this->tx_service->transaction(function() use($user_id){
            $user = $this->repository->get($user_id);
            if(is_null($user))  throw new EntityNotFoundException();
            $user->active = true;
            $this->repository->update($user);
        });
    }

    /**
     * @param int $user_id
     * @return void
     * @throws EntityNotFoundException
     */
    public function deActivateUser($user_id)
    {
        $this->tx_service->transaction(function() use($user_id){
            $user = $this->repository->get($user_id);
            if(is_null($user)) throw new EntityNotFoundException();
            $user->active = false;
            $this->repository->update($user);
        });
    }

    /**
     * @param int $user_id
     * @param bool $show_pic
     * @param bool $show_full_name
     * @param bool $show_email
     * @return bool
     * @throws EntityNotFoundException
     */
    public function saveProfileInfo($user_id, $show_pic, $show_full_name, $show_email)
    {

        return $this->tx_service->transaction(function() use($user_id, $show_pic, $show_full_name, $show_email){
            $user = $this->repository->get($user_id);
            if(is_null($user)) throw new EntityNotFoundException();

            $user->public_profile_show_photo    = $show_pic;
            $user->public_profile_show_fullname = $show_full_name;
            $user->public_profile_show_email    = $show_email;

            $this->repository->update($user);
            return true;
        });
    }

    /**
     * @param Member $member
     * @return IOpenIdUser
     */
    public function buildUser(Member $member)
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