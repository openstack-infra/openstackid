<?php namespace Utils\Services;
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

/**
 * Interface ISecurityPolicy
 * implements Checkpoint Pattern
 * depicted on Architectural Patterns for Enabling Application Security - Yoder/Barcalow
 * Defines contract for a generic security policy
 * @package Utils\Services
 */
interface ISecurityPolicy {
    /**
     * Check if current security policy is meet or not
     * @return boolean
     */
    public function check();

    /**
     * Apply security policy on a exception
     * @param Exception $ex
     * @return $this
     */
    public function apply(Exception $ex);

    /**
     * @param ISecurityPolicyCounterMeasure $counter_measure
     * @return $this
     */
    public function setCounterMeasure(ISecurityPolicyCounterMeasure $counter_measure);
} 