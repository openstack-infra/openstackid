<?php
namespace services\oauth2;

use oauth2\models\IApi;
use oauth2\services\IApiService;
use Api;
use DB;
use  oauth2\exceptions\InvalidApi;
use  oauth2\exceptions\InvalidApiEndpoint;
use  oauth2\exceptions\InvalidApiScope;

class ApiService implements  IApiService {


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
                $res = $api->delete();
            }
        });
        return $res;
    }

    /**
     * @param $name
     * @param $description
     * @param $active
     * @param $resource_server_id
     * @return null|IApi
     */
    public function add($name, $description, $active, $resource_server_id)
    {
        $instance = null;
        if(is_string($active)){
            $active = $active==='true'?true:false;
        }

        DB::transaction(function () use ($name, $description, $active, $resource_server_id, &$instance) {
            $instance = new Api(
                array(
                    'name'               => $name,
                    'description'        => $description,
                    'active'             => $active,
                    'resource_server_id' => $resource_server_id
                )
            );

            $instance->Save();
        });
        return $instance;
    }

    /**
     * @param $id
     * @param array $params
     * @throws \oauth2\exceptions\InvalidApi
     */
    public function update($id, array $params){

        $api = Api::find($id);
        if(is_null($api))
            throw new InvalidApi(sprintf('api id %s does not exists!',$id));

        $allowed_update_params = array('name','description','active');
        foreach($allowed_update_params as $param){
            if(array_key_exists($param,$params)){
                $api->{$param} = $params[$param];
            }
        }
        return $this->save($api);
    }

    /**
     * @param IApi $api
     * @return void
     */
    public function save(IApi $api)
    {
        if(!$api->exists() || count($api->getDirty())>0){
            return $api->Save();
        }
        return false;
    }

    /**
     * @param $id
     * @param $active
     * @return bool
     */
    public function setStatus($id, $active)
    {
        $api = Api::find($id);
        if(is_null($api)) return false;
        return $api->update(array('active'=>$active));
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