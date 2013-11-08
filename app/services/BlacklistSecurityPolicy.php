<?php

namespace services;

use BannedIP;
use DB;
use Exception;
use Log;
use openid\services\ISecurityPolicy;
use openid\services\ISecurityPolicyCounterMeasure;
use UserExceptionTrail;
use \DateTime;

/**
 * Class BlacklistSecurityPolicy
 * implements check point security pattern
 * @package services
 */
class BlacklistSecurityPolicy implements ISecurityPolicy
{

    const BannedIpLifeTimeSeconds = 21600;// 6 hs
    const MinutesWithoutExceptions = 5;
    const ReplayAttackExceptionInitialDelay = 10;
    const MaxInvalidNonceAttempts = 10;
    const InvalidNonceInitialDelay = 10;
    const MaxInvalidOpenIdMessageExceptionAttempts = 10;
    const InvalidOpenIdMessageExceptionInitialDelay = 10;
    const MaxOpenIdInvalidRealmExceptionAttempts = 10;
    const OpenIdInvalidRealmExceptionInitialDelay = 10;
    const MaxInvalidOpenIdMessageModeAttempts = 10;
    const InvalidOpenIdMessageModeInitialDelay = 10;
    const MaxInvalidOpenIdAuthenticationRequestModeAttempts = 10;
    const InvalidOpenIdAuthenticationRequestModeInitialDelay = 10;
    const MaxAuthenticationExceptionAttempts = 10;
    const AuthenticationExceptionInitialDelay = 20;

    private $redis;
    private $counter_measure;

    public function __construct(ISecurityPolicyCounterMeasure $counter_measure)
    {
        $this->counter_measure = $counter_measure;
        $this->redis = \RedisLV4::connection();
    }

    public function check()
    {
        $res = true;
        $remote_address = IPHelper::getUserIp();

        try {
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

                    if (!$success)
                        throw new Exception("BlacklistSecurityPolicy->check : lock already taken!");

                    try {
                        $issued             = $banned_ip->created_at;
                        $issued             = $issued->date;
                        $issued             = DateTime::createFromFormat("Y-m-d H:i:s", $issued);
                        $utc_now            = gmdate("Y-m-d H:i:s", time());
                        $utc_now            = DateTime::createFromFormat("Y-m-d H:i:s", $utc_now);

                        //get time lived on seconds
                        $time_lived_seconds = abs($utc_now->getTimestamp()-$issued->getTimestamp());

                        if ($time_lived_seconds >= self::BannedIpLifeTimeSeconds) {
                            //void banned ip
                            $banned_ip->delete();
                        } else {
                            $banned_ip->hits = $banned_ip->hits + 1;
                            $banned_ip->Save();
                            //add ip back to redis
                            $success = $this->redis->setnx($banned_ip->ip, $banned_ip->hits);
                            if ($success) {
                                //set remaining time to live
                                $this->redis->expire($remote_address, (self::BannedIpLifeTimeSeconds - $time_lived_seconds) );
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

    public function apply(Exception $ex)
    {
        try {
            $remote_ip = IPHelper::getUserIp();
            $exception_class = get_class($ex);
            //check exception count by type on last "MinutesWithoutExceptions" minutes...
            $exception_count = UserExceptionTrail::where('from_ip', '=', $remote_ip)
                ->where('exception_type', '=', $exception_class)
                ->where('created_at', '>', DB::raw('( UTC_TIMESTAMP() - INTERVAL ' . self::MinutesWithoutExceptions . ' MINUTE )'))
                ->count();

            switch ($exception_class) {
                case 'openid\exceptions\ReplayAttackException':
                {
                    //on replay attack , ban ip..
                    $this->createBannedIP(self::ReplayAttackExceptionInitialDelay, $exception_class);
                }
                    break;
                case 'openid\exceptions\InvalidNonce':
                {
                    if ($exception_count >= self::MaxInvalidNonceAttempts)
                        $this->createBannedIP(self::InvalidNonceInitialDelay, $exception_class);
                }
                    break;
                case 'openid\exceptions\InvalidOpenIdMessageException':
                {
                    if ($exception_count >= self::MaxInvalidOpenIdMessageExceptionAttempts)
                        $this->createBannedIP(self::InvalidOpenIdMessageExceptionInitialDelay, $exception_class);
                }
                    break;
                case 'openid\exceptions\OpenIdInvalidRealmException':
                {
                    if ($exception_count >= self::MaxOpenIdInvalidRealmExceptionAttempts)
                        $this->createBannedIP(self::OpenIdInvalidRealmExceptionInitialDelay, $exception_class);
                }
                    break;
                case 'openid\exceptions\InvalidOpenIdMessageMode':
                {
                    if ($exception_count >= self::MaxInvalidOpenIdMessageModeAttempts)
                        $this->createBannedIP(self::InvalidOpenIdMessageModeInitialDelay, $exception_class);
                }
                    break;
                case 'openid\exceptions\InvalidOpenIdAuthenticationRequestMode':
                {
                    if ($exception_count >= self::MaxInvalidOpenIdAuthenticationRequestModeAttempts)
                        $this->createBannedIP(self::InvalidOpenIdAuthenticationRequestModeInitialDelay, $exception_class);
                }
                    break;
                case 'auth\exceptions\AuthenticationException':
                {
                    if ($exception_count >= self::MaxAuthenticationExceptionAttempts)
                        $this->createBannedIP(self::AuthenticationExceptionInitialDelay, $exception_class);
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
                $this->redis->expire($remote_address, self::BannedIpLifeTimeSeconds);
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
}