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
use Auth\Exceptions\AuthenticationInvalidPasswordAttemptException;
use Utils\Services\ISecurityPolicy;
use Utils\Services\ISecurityPolicyCounterMeasure;

/**
 * Class LockUserSecurityPolicy
 * @package Services\SecurityPolicies
 */
final class LockUserSecurityPolicy implements ISecurityPolicy
{
    /**
     * @var ISecurityPolicyCounterMeasure
     */
    private $counter_measure;
    /**
     * Check if current security policy is meet or not
     * @return bool
     */
    public function check()
    {
        return true;
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
     * Apply security policy on a exception
     * @param Exception $ex
     * @return $this
     */
    public function apply(Exception $ex)
    {
        try {
            if($ex instanceof AuthenticationInvalidPasswordAttemptException) {
                $user_identifier = $ex->getIdentifier();
                if (!is_null($user_identifier) && !empty($user_identifier))
                    $this->counter_measure->trigger(array('user_identifier' => $user_identifier));
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
        return $this;
    }
}