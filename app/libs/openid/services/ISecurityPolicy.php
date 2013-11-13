<?php

namespace openid\services;

use \Exception;

/**
 * Interface ISecurityPolicy
 * implements Checkpoint Pattern
 * depicted on Architectural Patterns for Enabling Application Security - Yoder/Barcalow
 * Defines contract for a generic security policy
 * @package services
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
     * @return mixed
     */
    public function apply(Exception $ex);

    public function setCounterMeasure(ISecurityPolicyCounterMeasure $counter_measure);
} 