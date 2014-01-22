<?php
namespace services\oauth2;

use oauth2\models\IApi;
use oauth2\services\IApiService;
use Api;
use DB;

class ApiService implements  IApiService {

    /**
     * @param $url
     * @param $http_method
     * @return IApi
     */
    public function getApiByUrlAndMethod($url, $http_method)
    {
        return Api::where('route','=',$url)->where('http_method','=',$http_method)->first();
    }

    /**
     * @param $api_id
     * @return IApi
     */
    public function get($api_id)
    {
        return Api::find($api_id);
    }

    /**
     * @param $api_name
     * @return IApi
     */
    public function getByName($api_name)
    {
        return Api::where('name','=',$api_name)->first();
    }

    /**
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        $res = false;
        DB::transaction(function () use ($id,&$res) {
            $api = Api::find($id);
            if(!is_null($api)){
                $api->delete();
                $res = true;
            }
        });
        return $res;
    }

    /**
     * @param $name
     * @param $description
     * @param $active
     * @param $route
     * @param $http_method
     * @param $resource_server_id
     * @return IApi
     */
    public function addApiEndpoint($name, $description, $active, $route, $http_method, $resource_server_id)
    {
        $instance = null;
        if(is_string($active)){
            $active = $active==='true'?true:false;
        }

        DB::transaction(function () use ($name, $description, $active, $route, $http_method, $resource_server_id, &$instance) {
            $instance = new Api(
                array(
                    'name'               => $name,
                    'description'        => $description,
                    'active'             => $active,
                    'route'              => $route,
                    'http_method'        => $http_method,
                    'resource_server_id' => $resource_server_id
                )
            );

            $instance->Save();
        });
        return $instance;
    }

    /**
     * @param IApi $endpoint
     * @return void
     */
    public function save(IApi $endpoint)
    {
        return $endpoint->Save();
    }

    /**
     * @param $id
     * @param $active
     * @return bool
     */
    public function setStatus($id, $active)
    {
        return Api::find($id)->update(array('active'=>$active));
    }

    /**
     * @param int $page_size
     * @param int $page_nbr
     * @return mixed
     */
    public function getAll($page_size=10,$page_nbr=1){
        DB::getPaginator()->setCurrentPage($page_nbr);
        return Api::paginate($page_size);
    }
}