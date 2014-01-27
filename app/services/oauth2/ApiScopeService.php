<?php

namespace services\oauth2;

use oauth2\exceptions\InvalidApi;
use oauth2\exceptions\InvalidApiScope;
use oauth2\models\IApiScope;
use oauth2\services\IApiScopeService;
use ApiScope;
use Api;
use DB;

/**
 * Class ApiScopeService
 * @package services\oauth2
 */
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

         $scopes = ApiScope
             ::with('api')
            ->with('api.resource_server')
            ->where('active','=',true)
            ->where('system','=',$system)
            ->orderBy('api_id')->get();

        $res = array();

        foreach($scopes as $scope){
            $api = $scope->api()->first();
            if(!is_null($api) && $api->resource_server()->first()->active && $api->active)
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

    /**
     * gets an api scope by id
     * @param $id id of api scope
     * @return IApiScope
     */
    public function get($id)
    {
        return ApiScope::find($id);
    }

    /**
     * Gets a paginated list of api scopes
     * @param int $page_size
     * @param int $page_nbr
     * @return mixed
     */
    public function getAll($page_size = 10, $page_nbr = 1)
    {
        DB::getPaginator()->setCurrentPage($page_nbr);
        return ApiScope::paginate($page_size);
    }

    /**
     * @param IApiScope $scope
     * @return bool
     */
    public function save(IApiScope $scope)
    {
        if(!$scope->exists() || count($scope->getDirty())>0){
            return $scope->Save();
        }
        return false;
    }

    /**
     * @param $id
     * @param array $params
     * @return bool
     * @throws \oauth2\exceptions\InvalidApiScope
     */
    public function update($id, array $params)
    {
        $scope = ApiScope::find($id);
        if(is_null($scope))
            throw new InvalidApiScope(sprintf('scope id %s does not exists!',$id));

        $allowed_update_params = array('name','description','short_description','active','system','default');

        foreach($allowed_update_params as $param){
            if(array_key_exists($param,$params)){
                $scope->{$param} = $params[$param];
            }
        }
        return $this->save($scope);
    }

    /**
     * sets api scope status (active/deactivated)
     * @param $id id of api scope
     * @param bool $status status (active/non active)
     * @return void
     */
    public function setStatus($id, $status)
    {
        $scope = ApiScope::find($id);
        if(is_null($scope)) return false;
        return $scope->update(array('active'=>$status));
    }

    /**
     * deletes an api scope
     * @param $id id of api scope
     * @return bool
     */
    public function delete($id)
    {
        $res = false;
        DB::transaction(function () use ($id,&$res) {
            $api = ApiScope::find($id);
            if(!is_null($api)){
                $res = $api->delete();
            }
        });
        return $res;
    }

    /**
     * Creates a new api scope instance
     * @param $name
     * @param $short_description
     * @param $description
     * @param $active
     * @param $default
     * @param $system
     * @param $api_id
     * @throws \oauth2\exceptions\InvalidApi
     * @return IApiScope
     */
    public function add($name, $short_description, $description, $active, $default, $system, $api_id)
    {
        $instance = null;
        DB::transaction(function () use ($name, $short_description, $description, $active, $default, $system, $api_id, &$instance) {

            if(is_null(Api::find($api_id)))
                throw new InvalidApi(sprintf('api id %s does not exists!.',$api_id));

            $instance = new ApiScope(
                array(
                    'name'              => $name,
                    'description'       => $description,
                    'short_description' => $short_description,
                    'active'            => $active,
                    'default'           => $default,
                    'system'            => $system,
                    'api_id'            => $api_id
                )
            );

            $instance->Save();
        });
        return $instance;
    }
}