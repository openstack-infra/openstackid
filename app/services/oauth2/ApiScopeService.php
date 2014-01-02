<?php

namespace services\oauth2;

use oauth2\services\IApiScopeService;
use ApiScope;

class ApiScopeService implements IApiScopeService {

    /**
     * @param array $scopes_names
     * @return mixed
     */
    public function getScopesByName(array $scopes_names)
    {
        return ApiScope::where('active','=',true)->whereIn('name',$scopes_names)->get();
    }

    /** get all active scopes
     * @return mixed
     */
    public function getAvailableScopes(){
        return ApiScope::where('active','=',true)->where('system','=',false)->get();
    }

    public function getAudienceByScopeNames(array $scopes_names){
        $scopes = $this->getScopesByName($scopes_names);
        $audience = array();
        foreach($scopes as $scope){
            $api = $scope->api()->first();
            $resource_server = $api->resource_server()->first();
            if(!array_key_exists($resource_server->host, $audience)){
                $audience[$resource_server->host] = $resource_server->ip;
            }
        }
        return $audience;
    }


}