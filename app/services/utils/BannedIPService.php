<?php

namespace services\utils;

use BannedIP;
use DB;
use Log;
use Auth;
use utils\services\ICacheService;
use utils\services\IServerConfigurationService;
use utils\services\IBannedIPService;
use utils\services\IAuthService;
use utils\services\ILogService;

/**
 * Class BannedIPService
 * @package utils\services
 */
class BannedIPService implements IBannedIPService {

    private $cache_service;
    private $server_configuration_service;
    private $log_service;
    private $auth_service;

    /**
     * @param ICacheService $cache_service
     * @param IServerConfigurationService $server_configuration_service
     * @param IAuthService $auth_service
     * @param ILogService $log_service
     */
    public function __construct(ICacheService $cache_service,
                                IServerConfigurationService $server_configuration_service,
                                IAuthService $auth_service,
                                ILogService $log_service){

        $this->cache_service                = $cache_service;
        $this->server_configuration_service = $server_configuration_service;
        $this->log_service                  = $log_service;
        $this->auth_service                 = $auth_service;
    }

    /**
     * @param $initial_hits
     * @param $exception_type
     * @return bool
     */
    public function add($initial_hits, $exception_type)
    {
        $res = false;
        try {
            $remote_address = Request::server('REMOTE_ADDR');
            //try to create on cache
            $this->cache_service->addSingleValue($remote_address, $initial_hits, intval($this->server_configuration_service->getConfigValue("BlacklistSecurityPolicy.BannedIpLifeTimeSeconds")));

            DB::transaction(function () use ($remote_address, $exception_type, $initial_hits,&$res) {

                $banned_ip = BannedIP::where("ip", "=", $remote_address)->first();
                if (!$banned_ip) {
                    $banned_ip = new BannedIP();
                    $banned_ip->ip = $remote_address;
                }
                $banned_ip->exception_type = $exception_type;
                $banned_ip->hits           = $initial_hits;

	            if(Auth::check()){
		            $banned_ip->user_id    = Auth::user()->getId();
	            }

	            $res = $banned_ip->Save();
            });

        } catch (Exception $ex) {
            $this->log_service->error($ex);
            $res = false;
        }
        return $res;
    }

    public function delete($ip)
    {
        $res           = false;
	    $cache_service = $this->cache_service;
		$this_var      = $this;
	    DB::transaction(function () use ($ip,&$res,&$cache_service,&$this_var) {

            if($banned_ip = $this_var->getByIP($ip)){
                $res = $banned_ip->delete();
	            $cache_service->delete($ip);
            }
        });
        return $res;
    }

    public function get($id)
    {
        return BannedIP::find($id);
    }

    public function getByIP($ip){
        return BannedIP::where('ip','=',$ip)->first();
    }

    public function getByPage($page_nbr = 1, $page_size = 10, array $filters = array(), array $fields = array('*'))
    {
        DB::getPaginator()->setCurrentPage($page_nbr);
        return BannedIP::Filter($filters)->paginate($page_size,$fields);
    }
}