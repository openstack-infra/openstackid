<?php

namespace services;

use DB;
use Exception;
use Log;
use auth\exceptions\AuthenticationInvalidPasswordAttemptException;
use utils\services\ISecurityPolicy;
use utils\services\ISecurityPolicyCounterMeasure;

/**
 * Class LockUserSecurityPolicy
 * @package services
 */
final class LockUserSecurityPolicy implements ISecurityPolicy
{
    private $counter_measure;
    /**
     * Check if current security policy is meet or not
     * @return boolean
     */
    public function check()
    {
        return true;
    }

    public function setCounterMeasure(ISecurityPolicyCounterMeasure $counter_measure)
    {
        $this->counter_measure = $counter_measure;
    }

    /**
     * Apply security policy on a exception
     * @param Exception $ex
     * @return void
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
    }
}