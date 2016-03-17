<?php

namespace services\utils;

use Exception;
use Log;
use Auth;
use utils\services\ICheckPointService;
use utils\services\ISecurityPolicy;
use utils\IPHelper;
use UserExceptionTrail;

class CheckPointService implements ICheckPointService
{

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

    public function addPolicy(ISecurityPolicy $policy)
    {
        array_push($this->policies, $policy);
    }
}
