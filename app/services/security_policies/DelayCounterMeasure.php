<?php

namespace services;

use Exception;
use Log;
use utils\services\ICacheService;
use utils\services\ISecurityPolicyCounterMeasure;
use utils\IPHelper;

/**
 * Class DelayCounterMeasure
 * @package services
 */
class DelayCounterMeasure implements ISecurityPolicyCounterMeasure
{
    private $cache_service;

    public function __construct(ICacheService $cache_service){
        $this->cache_service = $cache_service;
    }

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
    }
}