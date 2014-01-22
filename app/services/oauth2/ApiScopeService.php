<?php

namespace services\oauth2;

use oauth2\services\IApiScopeService;
use ApiScope;
use DB;

class ApiScopeService implements IApiScopeService {

    /**
     * @param array $scopes_names
     * @return mixed
     */
    public function getScopesByName(array $scopes_names)
    {
        return ApiScope::where('active','=',true)->whereIn('name',$scopes_names)->get();
    }

    public function getFriendlyScopesByName(array $scopes_names){

        return DB::table('oauth2_api_scope')->where('active','=',true)->whereIn('name',$scopes_names)->lists('short_description');
    }

    /**
     * @param bool $system
     * @return array|mixed
     */
    public function getAvailableScopes($system=false){

         $scopes = ApiScope::with('api')
            ->where('active','=',true)
            ->where('system','=',$system)
            ->orderBy('api_id')->get();

        $res = array();

        foreach($scopes as $scope){
            $api = $scope->api()->first();
            if($api->active && $api->resource_server()->first()->active)
                array_push($res,$scope);
        }
        return $res;
    }

    public function getAudienceByScopeNames(array $scopes_names){
        $scopes = $this->getScopesByName($scopes_names);
        $audience = array();
        foreach($scopes as $scope){
            $api = $scope->api()->first();
            $resource_server = !is_null($api)? $api->resource_server()->first():null;
            if(!is_null($resource_server) && !array_key_exists($resource_server->host, $audience)){
                $audience[$resource_server->host] = $resource_server->ip;
            }
        }
        return $audience;
    }

    public function getStrAudienceByScopeNames(array $scopes_names){
        $audiences = $this->getAudienceByScopeNames($scopes_names);
        $audience  = '';
        foreach($audiences as $resource_server_host => $ip){
            $audience = $audience . $resource_server_host .' ';
        }
        $audience  = trim($audience);
        return $audience;
    }

}