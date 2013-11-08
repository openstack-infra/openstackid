<?php

namespace services;

use \Log;
use openid\services\ISecurityPolicyCounterMeasure;

class DelayCounterMeasure implements ISecurityPolicyCounterMeasure
{
    private $redis;

    public function __construct(){
        $this->redis = \RedisLV4::connection();
    }

    public function trigger()
    {
        try {
            $remote_address = IPHelper::getUserIp();
            if ($this->redis->exists($remote_address)) {
                Log::warning(sprintf("DelayCounterMeasure: attempt from banned ip %s",$remote_address));
                $hits = $this->redis->get($remote_address);
                sleep(2 ^ $hits);
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }
}