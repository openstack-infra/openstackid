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

use Exception;
use Illuminate\Support\Facades\Log;
use Utils\IPHelper;
use Utils\Services\ICacheService;
use Utils\Services\ISecurityPolicyCounterMeasure;

/**
 * Class DelayCounterMeasure
 * @package Services\SecurityPolicies
 */
class DelayCounterMeasure implements ISecurityPolicyCounterMeasure
{
    /**
     * @var ICacheService
     */
    private $cache_service;

    /**
     * DelayCounterMeasure constructor.
     * @param ICacheService $cache_service
     */
    public function __construct(ICacheService $cache_service){
        $this->cache_service = $cache_service;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function trigger(array $params = array())
    {
        try {
            $remote_address = IPHelper::getUserIp();
            if ($this->cache_service->exists($remote_address)) {
                Log::warning(sprintf("DelayCounterMeasure: attempt from banned ip %s",$remote_address));
                $hits = intval($this->cache_service->getSingleValue($remote_address));
                sleep(2 ^ $hits);
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
        return $this;
    }
}