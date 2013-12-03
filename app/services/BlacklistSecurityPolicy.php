<?php

namespace services;

use BannedIP;
use DateTime;
use DB;
use Exception;
use Log;
use openid\services\IServerConfigurationService;
use UserExceptionTrail;

/**
 * Class BlacklistSecurityPolicy
 * implements check point security pattern
 * @package services
 */
class BlacklistSecurityPolicy implements \utils\services\ISecurityPolicy
{

    private $server_configuration_service;
    private $redis;
    private $counter_measure;

    public function __construct(IServerConfigurationService $server_configuration_service)
    {

        $this->redis = \RedisLV4::connection();
        $this->server_configuration_service = $server_configuration_service;
    }

    /**
     * Check policy
     * @return bool
     */
    public function check()
    {
        $res = true;
        $remote_address = IPHelper::getUserIp();

        try {
            //check if banned ip is on redis ...
            if ($this->redis->exists($remote_address)) {
                $this->redis->incr($remote_address);
                $res = false;
            } else {
                //check on db
                $banned_ip = BannedIP::where("ip", "=", $remote_address)->first();
                //if exists ?
                if ($banned_ip) {
                    //set lock
                    $success = $this->redis->setnx("lock." . $remote_address, 1);

                    if (!$success) // if we cant get lock, then error
                        throw new Exception(sprintf("BlacklistSecurityPolicy->check : lock already taken for banned ip %s!", $banned_ip->ip));

                    try {

                        $issued = $banned_ip->created_at;
                        $utc_now = gmdate("Y-m-d H:i:s", time());
                        $utc_now = DateTime::createFromFormat("Y-m-d H:i:s", $utc_now);

                        //get time lived on seconds
                        $time_lived_seconds = abs($utc_now->getTimestamp() - $issued->getTimestamp());

                        if ($time_lived_seconds >= $this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.BannedIpLifeTimeSeconds")) {
                            //void banned ip
                            $banned_ip->delete();
                        } else {
                            $banned_ip->hits = $banned_ip->hits + 1;
                            $banned_ip->Save();
                            //add ip back to redis
                            $success = $this->redis->setnx($banned_ip->ip, $banned_ip->hits);
                            if ($success) {
                                //set remaining time to live
                                $this->redis->expire($remote_address, ($this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.BannedIpLifeTimeSeconds") - $time_lived_seconds));
                            }
                            $res = false;
                            //release lock
                            $this->redis->del("lock." . $remote_address);
                        }
                    } catch (Exception $ex) {
                        //release lock
                        $this->redis->del("lock." . $remote_address);
                        Log::error($ex);
                        $res = false;
                    }
                }
            }
            if (!$res)
                $this->counter_measure->trigger();
        } catch (Exception $ex) {
            Log::error($ex);
            $res = false;
        }
        return $res;
    }

    /**
     * Apply security policy
     * @param Exception $ex
     * @return mixed|void
     */
    public function apply(Exception $ex)
    {
        try {
            $remote_ip = IPHelper::getUserIp();
            $exception_class = get_class($ex);
            //check exception count by type on last "MinutesWithoutExceptions" minutes...
            $exception_count = UserExceptionTrail::where('from_ip', '=', $remote_ip)
                ->where('exception_type', '=', $exception_class)
                ->where('created_at', '>', DB::raw('( UTC_TIMESTAMP() - INTERVAL ' . $this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.MinutesWithoutExceptions") . ' MINUTE )'))
                ->count();

            switch ($exception_class) {
                case 'openid\exceptions\ReplayAttackException':
                {
                    //on replay attack , ban ip..
                    $this->createBannedIP($this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.ReplayAttackExceptionInitialDelay"), $exception_class);
                }
                    break;
                case 'openid\exceptions\InvalidNonce':
                {
                    if ($exception_count >= $this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.MaxInvalidNonceAttempts"))
                        $this->createBannedIP($this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.InvalidNonceInitialDelay"), $exception_class);
                }
                    break;
                case 'openid\exceptions\InvalidOpenIdMessageException':
                {
                    if ($exception_count >= $this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.MaxInvalidOpenIdMessageExceptionAttempts"))
                        $this->createBannedIP($this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.InvalidOpenIdMessageExceptionInitialDelay"), $exception_class);
                }
                    break;
                case 'openid\exceptions\OpenIdInvalidRealmException':
                {
                    if ($exception_count >= $this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.MaxOpenIdInvalidRealmExceptionAttempts"))
                        $this->createBannedIP($this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.OpenIdInvalidRealmExceptionInitialDelay"), $exception_class);
                }
                    break;
                case 'openid\exceptions\InvalidOpenIdMessageMode':
                {
                    if ($exception_count >= $this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.MaxInvalidOpenIdMessageModeAttempts"))
                        $this->createBannedIP($this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.InvalidOpenIdMessageModeInitialDelay"), $exception_class);
                }
                    break;
                case 'openid\exceptions\InvalidOpenIdAuthenticationRequestMode':
                {
                    if ($exception_count >= $this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.MaxInvalidOpenIdAuthenticationRequestModeAttempts"))
                        $this->createBannedIP($this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.InvalidOpenIdAuthenticationRequestModeInitialDelay"), $exception_class);
                }
                    break;
                case 'auth\exceptions\AuthenticationException':
                {
                    if ($exception_count >= $this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.MaxAuthenticationExceptionAttempts"))
                        $this->createBannedIP($this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.AuthenticationExceptionInitialDelay"), $exception_class);
                }
                    break;
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

    /**
     * internal function to create a new banned ip
     * @param $initial_hits
     * @param $exception_type
     */
    private function createBannedIP($initial_hits, $exception_type)
    {
        try {
            $remote_address = IPHelper::getUserIp();
            //try to create on redis
            $success = $this->redis->setnx($remote_address, $initial_hits);
            if ($success) {
                $this->redis->expire($remote_address, $this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.BannedIpLifeTimeSeconds"));
            }

            Log::warning(sprintf("BlacklistSecurityPolicy: Banning ip %s by Exception %s", $remote_address, $exception_type));
            //try to create on db
            $banned_ip = BannedIP::where("ip", "=", $remote_address)->first();
            if (!$banned_ip) {
                $banned_ip = new BannedIP();
                $banned_ip->ip = $remote_address;
            }
            $banned_ip->exception_type = $exception_type;
            $banned_ip->hits = $initial_hits;
            $banned_ip->Save();
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

    public function setCounterMeasure(\utils\services\ISecurityPolicyCounterMeasure $counter_measure)
    {
        $this->counter_measure = $counter_measure;
    }
}
