<?php namespace Services;
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

use Auth\Repositories\IUserRepository;
use Exception;
use Models\UserAction;
use Illuminate\Support\Facades\Log;
use Utils\Db\ITransactionService;

/**
 * Class UserActionService
 * @package Services
 */
final class UserActionService implements IUserActionService
{
    /**
     * @var IUserRepository
     */
    private $user_repository;
    /**
     * @var ITransactionService
     */
    private $tx_service;

    public function __construct(IUserRepository $user_repository, ITransactionService $tx_service)
    {
        $this->user_repository = $user_repository;
        $this->tx_service      = $tx_service;
    }

    /**
     * @param int $user_id
     * @param string $ip
     * @param string $user_action
     * @param null|string $realm
     * @return bool
     */
    public function addUserAction($user_id, $ip, $user_action, $realm = null)
    {
        return $this->tx_service->transaction(function() use($user_id, $ip, $user_action, $realm){
            try {

                $action              = new UserAction();
                $action->from_ip     = $ip;
                $action->user_action = $user_action;
                $action->realm       = $realm;
                $user                = $this->user_repository->get($user_id);

                if ($user) {
                    $user->actions()->save($action);
                    return true;
                }
                return false;
            } catch (Exception $ex) {
                Log::error($ex);
                return false;
            }
        });

    }
} 