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

use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Models\BannedIP;
use Models\IWhiteListedIPRepository;
use Models\UserExceptionTrail;
use OAuth2\Repositories\IResourceServerRepository;
use Utils\Db\ITransactionService;
use Utils\Exceptions\UnacquiredLockException;
use Utils\IPHelper;
use Utils\Services\ICacheService;
use Utils\Services\ILockManagerService;
use Utils\Services\IServerConfigurationService;

/**
 * Class BlacklistSecurityPolicy
 * implements check point security pattern
 * @package Services\SecurityPolicies
 */
class BlacklistSecurityPolicy extends AbstractBlacklistSecurityPolicy
{

    /**
     * @var array
     */
    private $exception_dictionary = array();

    /**
     * @var IResourceServerRepository
     */
    private $resource_server_repository;

    /**
     * @var IWhiteListedIPRepository
     */
    private $white_listed_ip_repository;

    /**
     * BlacklistSecurityPolicy constructor.
     * @param IWhiteListedIPRepository $white_listed_ip_repository
     * @param IServerConfigurationService $server_configuration_service
     * @param ILockManagerService $lock_manager_service
     * @param ICacheService $cache_service
     * @param IResourceServerRepository $resource_server_repository
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        IWhiteListedIPRepository    $white_listed_ip_repository,
        IServerConfigurationService $server_configuration_service,
        ILockManagerService         $lock_manager_service,
        ICacheService               $cache_service,
        IResourceServerRepository   $resource_server_repository,
        ITransactionService         $tx_service
    )
    {
        parent::__construct($server_configuration_service, $lock_manager_service, $cache_service, $tx_service);
        $this->resource_server_repository = $resource_server_repository;
        // here we configure on which exceptions are we interested and the max occurrence attempts and initial delay on tar pit for
        // offending IP address
        $this->exception_dictionary = array(
            'openid\exceptions\ReplayAttackException' => array(
                'BlacklistSecurityPolicy.MaxReplayAttackExceptionAttempts',
                'BlacklistSecurityPolicy.ReplayAttackExceptionInitialDelay'
            ),
            'openid\exceptions\InvalidNonce' => array(
                'BlacklistSecurityPolicy.MaxInvalidNonceAttempts',
                'BlacklistSecurityPolicy.InvalidNonceInitialDelay'
            ),
            'openid\exceptions\InvalidOpenIdMessageException' => array(
                'BlacklistSecurityPolicy.MaxInvalidOpenIdMessageExceptionAttempts',
                'BlacklistSecurityPolicy.InvalidOpenIdMessageExceptionInitialDelay'
            ),
            'openid\exceptions\OpenIdInvalidRealmException' => array(
                'BlacklistSecurityPolicy.MaxOpenIdInvalidRealmExceptionAttempts',
                'BlacklistSecurityPolicy.OpenIdInvalidRealmExceptionInitialDelay'
            ),
            'openid\exceptions\InvalidOpenIdMessageMode' => array(
                'BlacklistSecurityPolicy.MaxInvalidOpenIdMessageModeAttempts',
                'BlacklistSecurityPolicy.InvalidOpenIdMessageModeInitialDelay'
            ),
            'openid\exceptions\InvalidOpenIdAuthenticationRequestMode' => array(
                'BlacklistSecurityPolicy.MaxInvalidOpenIdAuthenticationRequestModeAttempts',
                'BlacklistSecurityPolicy.InvalidOpenIdAuthenticationRequestModeInitialDelay'
            ),
            'openid\exceptions\InvalidAssociation' => array(
                'BlacklistSecurityPolicy.MaxInvalidAssociationAttempts',
                'BlacklistSecurityPolicy.InvalidAssociationInitialDelay'
            ),
            'auth\exceptions\AuthenticationException' => array(
                'BlacklistSecurityPolicy.MaxAuthenticationExceptionAttempts',
                'BlacklistSecurityPolicy.AuthenticationExceptionInitialDelay'
            ),
            'oauth2\exceptions\ReplayAttackException' => array(
                'BlacklistSecurityPolicy.OAuth2.MaxAuthCodeReplayAttackAttempts',
                'BlacklistSecurityPolicy.OAuth2.AuthCodeReplayAttackInitialDelay'
            ),
            'oauth2\exceptions\InvalidAuthorizationCodeException' => array(
                'BlacklistSecurityPolicy.OAuth2.MaxInvalidAuthorizationCodeAttempts',
                'BlacklistSecurityPolicy.OAuth2.InvalidAuthorizationCodeInitialDelay'
            ),
            'oauth2\exceptions\BearerTokenDisclosureAttemptException' => array(
                'BlacklistSecurityPolicy.OAuth2.MaxInvalidBearerTokenDisclosureAttempt',
                'BlacklistSecurityPolicy.OAuth2.BearerTokenDisclosureAttemptInitialDelay'
            ),
        );
        $this->white_listed_ip_repository = $white_listed_ip_repository;
    }

    /**
     * Check policy
     * @return bool
     */
    public function check()
    {
        $res            = true;
        $remote_address = IPHelper::getUserIp();
        try {

            if($this->isIPAddressWhitelisted($remote_address)) return true;

            //check if banned ip is on cache ...
            if ($this->cache_service->incCounterIfExists($remote_address)) {
                $this->counter_measure->trigger();
                return false;
            }
            //check on db
            if (!is_null($banned_ip = BannedIP::where("ip", "=", $remote_address)->first())) {
                // banned ip exists on DB, set lock
                $this->lock_manager_service->acquireLock("lock.ip." . $remote_address);
                try {
                    //check lifetime
                    $issued = $banned_ip->created_at;
                    $utc_now = gmdate("Y-m-d H:i:s", time());
                    $utc_now = DateTime::createFromFormat("Y-m-d H:i:s", $utc_now);
                    //get time lived on seconds
                    $time_lived_seconds = abs($utc_now->getTimestamp() - $issued->getTimestamp());
                    if ($time_lived_seconds >= intval($this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.BannedIpLifeTimeSeconds"))) {
                        //void banned ip
                        $banned_ip->delete();

                        return true;
                    }

                    $banned_ip->hits = $banned_ip->hits + 1;
                    $banned_ip->Save();
                    //save ip on cache
                    $this->cache_service->addSingleValue($banned_ip->ip, $banned_ip->hits,
                        intval($this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.BannedIpLifeTimeSeconds") - $time_lived_seconds));
                } catch (Exception $ex) {
                    Log::error($ex);
                }
                //release lock
                $this->lock_manager_service->releaseLock("lock.ip." . $remote_address);
                $this->counter_measure->trigger();

                return false;
            }
        } catch (UnacquiredLockException $ex1) {
            Log::error($ex1);
            $res = false;
        } catch (Exception $ex) {
            Log::error($ex);
            $res = false;
        }
        return $res;
    }

    /**
     * @param Exception $ex
     * @throws Exception
     * @return $this
     */
    public function apply(Exception $ex)
    {
        try
        {
            $remote_ip       = IPHelper::getUserIp();
            $exception_class = get_class($ex);

            //check exception count by type on last "MinutesWithoutExceptions" minutes...
            $exception_count = intval(UserExceptionTrail::where('from_ip', '=', $remote_ip)
                ->where('exception_type', '=', $exception_class)
                ->where('created_at', '>',
                    DB::raw('( UTC_TIMESTAMP() - INTERVAL ' . $this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.MinutesWithoutExceptions") . ' MINUTE )'))
                ->count());

            if (array_key_exists($exception_class, $this->exception_dictionary))
            {
                $params = $this->exception_dictionary[$exception_class];
                $max_attempts = !is_null($params[0]) && !empty($params[0]) ? intval($this->server_configuration_service->getConfigValue($params[0])) : 0;

                Log::info
                (
                    sprintf
                    (
                        'IP %s, - exception_class %s - exception_count %s - max allowed attempts %s',
                        $remote_ip,
                        $exception_class,
                        $exception_count,
                        $max_attempts
                    )
                );

                $initial_delay_on_tar_pit = intval($this->server_configuration_service->getConfigValue($params[1]));
                if (!$this->isIPAddressWhiteListed($remote_ip) && $exception_count >= $max_attempts)
                {
                    Log::warning
                    (
                        sprintf
                        (
                            'banning IP %s, - exception_class %s - exception_count %s - max allowed attempts %s',
                            $remote_ip,
                            $exception_class,
                            $exception_count,
                            $max_attempts
                        )
                    );

                    $this->createBannedIP($initial_delay_on_tar_pit, $exception_class);
                }
            }
        }
        catch (Exception $ex)
        {
            Log::error($ex);
            throw $ex;
        }
        return $this;
    }

    /**
     * @param string $ip
     * @return bool
     */
    private function isIPAddressWhiteListed($ip)
    {
        $cache_value = $this->cache_service->getSingleValue($ip.".whitelisted");
        if(!empty($cache_value)) return true;

        $resource_server = $this->resource_server_repository->getByIp($ip);
        $white_listed_ip = $this->white_listed_ip_repository->getByIp($ip);

        $white_listed    = !is_null($resource_server) || !is_null($white_listed_ip);
        if($white_listed)
            $this->cache_service->setSingleValue($ip.".whitelisted", $ip.".whitelisted");

        return $white_listed;
    }

}




