<?php namespace Services\SecurityPolicies;
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
use Illuminate\Support\Facades\Log;
use OpenId\Services\IUserService;
use Utils\Services\ISecurityPolicyCounterMeasure;
use Utils\Services\IServerConfigurationService;

/**
 * Class LockUserCounterMeasure
 * @package Services\SecurityPolicies
 */
class LockUserCounterMeasure implements ISecurityPolicyCounterMeasure
{
    /**
     * @var IServerConfigurationService
     */
    private $server_configuration;
    /**
     * @var IUserService
     */
    private $user_service;
    /**
     * @var IUserRepository
     */
    private $repository;

    /**
     * LockUserCounterMeasure constructor.
     * @param IUserRepository $repository
     * @param IUserService $user_service
     * @param IServerConfigurationService $server_configuration
     */
    public function __construct(
        IUserRepository $repository,
        IUserService $user_service,
        IServerConfigurationService $server_configuration
    ) {
        $this->user_service = $user_service;
        $this->server_configuration = $server_configuration;
        $this->repository = $repository;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function trigger(array $params = array())
    {
        try {
            if (isset($params["user_identifier"])) {
                $user_identifier = $params["user_identifier"];
                $user            = $this->repository->getByExternalId($user_identifier);
                if (!is_null($user)) {
                    //apply lock policy
                    if (intval($user->login_failed_attempt) < intval($this->server_configuration->getConfigValue("MaxFailed.Login.Attempts"))) {
                        $this->user_service->updateFailedLoginAttempts($user->id);
                    } else {
                        $this->user_service->lockUser($user->id);
                    }
                }
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
        return $this;
    }
}