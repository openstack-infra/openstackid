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

use Illuminate\Support\Facades\Auth;
use Models\BannedIP;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Utils\Db\ITransactionService;
use Utils\IPHelper;
use Utils\Services\ICacheService;
use Utils\Services\ILockManagerService;
use Utils\Services\ISecurityPolicy;
use Utils\Services\ISecurityPolicyCounterMeasure;
use Utils\Services\IServerConfigurationService;
use Exception;
/**
 * Class AbstractBlacklistSecurityPolicy
 * @package Services\SecurityPolicies
 */
abstract class AbstractBlacklistSecurityPolicy implements ISecurityPolicy
{

    /**
     * @var IServerConfigurationService
     */
    protected $server_configuration_service;
    /**
     * @var ISecurityPolicyCounterMeasure
     */
    protected $counter_measure;
    /**
     * @var ILockManagerService
     */
    protected $lock_manager_service;
    /**
     * @var ICacheService
     */
    protected $cache_service;
    /**
     * @var ITransactionService
     */
    protected $tx_service;

    /**
     * @param IServerConfigurationService $server_configuration_service
     * @param ILockManagerService $lock_manager_service
     * @param ICacheService $cache_service
     * @param ITransactionService $tx_service
     */
    public function __construct(
        IServerConfigurationService $server_configuration_service,
        ILockManagerService $lock_manager_service,
        ICacheService $cache_service,
        ITransactionService $tx_service
    ) {
        $this->server_configuration_service = $server_configuration_service;
        $this->lock_manager_service = $lock_manager_service;
        $this->cache_service = $cache_service;
        $this->tx_service = $tx_service;
    }

    /**
     * @param ISecurityPolicyCounterMeasure $counter_measure
     * @return $this
     */
    public function setCounterMeasure(ISecurityPolicyCounterMeasure $counter_measure)
    {
        $this->counter_measure = $counter_measure;
        return $this;
    }

    /**
     * internal function to create a new banned ip
     * @param $initial_hits
     * @param $exception_type
     */
    protected function createBannedIP($initial_hits, $exception_type)
    {
        try {
            $remote_address = IPHelper::getUserIp();
            //try to create on cache
            $this->cache_service->addSingleValue($remote_address, $initial_hits,
                intval($this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.BannedIpLifeTimeSeconds")));

            Log::warning(sprintf("AbstractBlacklistSecurityPolicy: Banning ip %s by Exception %s", $remote_address,
                $exception_type));
            //try to create on db

            $this->tx_service->transaction(function () use ($remote_address, $exception_type, $initial_hits) {

                $banned_ip = BannedIP::where("ip", "=", $remote_address)->first();

                if (!$banned_ip) {
                    $banned_ip = new BannedIP();
                    $banned_ip->ip = $remote_address;
                }
                $banned_ip->exception_type = $exception_type;
                $banned_ip->hits = $initial_hits;

                if (Auth::check()) {
                    $banned_ip->user_id = Auth::user()->getId();
                }

                $banned_ip->Save();
            });

        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

} 