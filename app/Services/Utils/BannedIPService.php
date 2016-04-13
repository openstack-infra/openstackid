<?php namespace Services\Utils;
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
use Illuminate\Support\Facades\Auth;
use Models\BannedIP;
use Illuminate\Support\Facades\DB;
use Exception;
use Utils\Db\ITransactionService;
use Utils\Services\IAuthService;
use Utils\Services\IBannedIPService;
use Utils\Services\ICacheService;
use Utils\Services\ILogService;
use Utils\Services\IServerConfigurationService;

/**
 * Class BannedIPService
 * @package Utils\Services
 */
class BannedIPService implements IBannedIPService
{

    /**
     * @var ICacheService
     */
    private $cache_service;
    /**
     * @var IServerConfigurationService
     */
    private $server_configuration_service;
    /**
     * @var ILogService
     */
    private $log_service;
    /**
     * @var IAuthService
     */
    private $auth_service;
    /**
     * @var ITransactionService
     */
    private $tx_service;

    /**
     * @param ICacheService $cache_service
     * @param IServerConfigurationService $server_configuration_service
     * @param IAuthService $auth_service
     * @param ILogService $log_service
     * @param ITransactionService $tx_service
     */
    public function __construct(
        ICacheService $cache_service,
        IServerConfigurationService $server_configuration_service,
        IAuthService $auth_service,
        ILogService $log_service,
        ITransactionService $tx_service
    ) {

        $this->cache_service = $cache_service;
        $this->server_configuration_service = $server_configuration_service;
        $this->log_service = $log_service;
        $this->auth_service = $auth_service;
        $this->tx_service = $tx_service;
    }

    /**
     * @param $initial_hits
     * @param $exception_type
     * @return bool
     */
    public function add($initial_hits, $exception_type)
    {
        $res = true;
        try {
            $remote_address = Request::server('REMOTE_ADDR');
            //try to create on cache
            $this->cache_service->addSingleValue($remote_address, $initial_hits,
                intval($this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.BannedIpLifeTimeSeconds")));

            $this->tx_service->transaction(function () use ($remote_address, $exception_type, $initial_hits, &$res) {

                $banned_ip = BannedIP::where("ip", "=", $remote_address)->first();
                if (!$banned_ip) {
                    $banned_ip = new BannedIP();
                    $banned_ip->ip = $remote_address;
                }
                $banned_ip->exception_type = $exception_type;
                $banned_ip->hits = $initial_hits;

                if (Auth::check()) {
                    $banned_ip->user_id = Auth::user()->getId();
                }

                $res = $banned_ip->Save();
            });

        } catch (Exception $ex) {
            $this->log_service->error($ex);
            $res = false;
        }
        return $res;
    }

    /**
     * @param $ip
     * @return bool
     */
    public function delete($ip)
    {
        $cache_service = $this->cache_service;
        return $this->tx_service->transaction(function () use ($ip, $cache_service) {
            $res = false;
            if ($banned_ip = $this->getByIP($ip)) {
                $res = $banned_ip->delete();
                $cache_service->delete($ip);
            }
            return $res;
        });
    }

    /**
     * @param int $id
     * @return BannedIP
     */
    public function get($id)
    {
        return BannedIP::find($id);
    }

    /**
     * @param int $ip
     * @return BannedIP
     */
    public function getByIP($ip)
    {
        return BannedIP::where('ip', '=', $ip)->first();
    }

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getByPage($page_nbr = 1, $page_size = 10, array $filters = array(), array $fields = array('*'))
    {
        DB::getPaginator()->setCurrentPage($page_nbr);

        return BannedIP::Filter($filters)->paginate($page_size, $fields);
    }
}