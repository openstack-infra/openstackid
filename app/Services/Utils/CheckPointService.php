<?php namespace Services\Utils;
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
use Illuminate\Support\Facades\Auth;
use Utils\Services\ICheckPointService;
use Utils\Services\ISecurityPolicy;
use Utils\IPHelper;
use Models\UserExceptionTrail;

/**
 * Class CheckPointService
 * @package Services\Utils
 */
class CheckPointService implements ICheckPointService
{
    /**
     * @var ISecurityPolicy[]
     */
    private $policies;

    public function __construct(ISecurityPolicy $policy)
    {
        $this->policies = array();
        array_push($this->policies, $policy);
    }

    public function check()
    {
        $res = false;
        try {
            foreach ($this->policies as $policy) {
                $res = $policy->check();
                if (!$res) break;
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
        return $res;
    }

    /**
     * Keeps track of exceptions
     * @param Exception $ex
     * @return mixed
     */
    public function trackException(Exception $ex)
    {
        try {
            $remote_ip                  = IPHelper::getUserIp();
            $class_name                 = get_class($ex);
            $user_trail                 = new UserExceptionTrail();
            $user_trail->from_ip        = $remote_ip;
            $user_trail->exception_type = $class_name;
            $user_trail->stack_trace    = $ex->getTraceAsString();
            if(Auth::check()){
                $user_trail->user_id = Auth::user()->getId();
            }
            $user_trail->Save();
            Log::warning(sprintf("* CheckPointService - exception : << %s >> - IP Address: %s",$ex->getMessage(),$remote_ip));
            //applying policies
            foreach ($this->policies as $policy) {
                $policy->apply($ex);
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

    /**
     * @param ISecurityPolicy $policy
     * @return $this
     */
    public function addPolicy(ISecurityPolicy $policy)
    {
        array_push($this->policies, $policy);
        return $this;
    }
}
