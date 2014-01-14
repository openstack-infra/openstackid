<?php

namespace services;

use BannedIP;
use DB;
use Log;
use utils\services\ICacheService;
use utils\services\ILockManagerService;
use utils\services\ISecurityPolicy;
use utils\services\ISecurityPolicyCounterMeasure;
use utils\services\IServerConfigurationService;

abstract class AbstractBlacklistSecurityPolicy implements ISecurityPolicy
{

    protected $server_configuration_service;
    protected $counter_measure;
    protected $lock_manager_service;
    protected $cache_service;

    public function __construct(IServerConfigurationService $server_configuration_service, ILockManagerService $lock_manager_service, ICacheService $cache_service)
    {

        $this->server_configuration_service = $server_configuration_service;
        $this->lock_manager_service = $lock_manager_service;
        $this->cache_service = $cache_service;
    }

    public function setCounterMeasure(ISecurityPolicyCounterMeasure $counter_measure)
    {
        $this->counter_measure = $counter_measure;
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
            $this->cache_service->addSingleValue($remote_address, $initial_hits, intval($this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.BannedIpLifeTimeSeconds")));

            Log::warning(sprintf("AbstractBlacklistSecurityPolicy: Banning ip %s by Exception %s", $remote_address, $exception_type));
            //try to create on db

            DB::transaction(function () use ($remote_address, $exception_type, $initial_hits) {
                $banned_ip = BannedIP::where("ip", "=", $remote_address)->first();
                if (!$banned_ip) {
                    $banned_ip = new BannedIP();
                    $banned_ip->ip = $remote_address;
                }
                $banned_ip->exception_type = $exception_type;
                $banned_ip->hits = $initial_hits;
                $banned_ip->Save();
            });

        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

} 