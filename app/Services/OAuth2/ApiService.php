<?php namespace Services\OAuth2;
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

use OAuth2\Models\IApi;
use Models\OAuth2\Api;
use OAuth2\Services\IApiService;
use OAuth2\Exceptions\InvalidApi;
use Utils\Db\ITransactionService;

/**
 * Class ApiService
 * @package Services\OAuth2
 */
class ApiService implements  IApiService {

	private $tx_service;

	/**
	 * @param ITransactionService $tx_service
	 */
	public function __construct(ITransactionService $tx_service){
		$this->tx_service = $tx_service;
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
     * @param string $api_name
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
	    $this->tx_service->transaction(function () use ($id,&$res) {
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
            $active =  strtoupper($active) == 'TRUE'?true:false;
        }

	    $this->tx_service->transaction(function () use ($name, $description, $active, $resource_server_id, &$instance) {

            $count = Api::where('name','=',$name)->where('resource_server_id','=',$resource_server_id)->count();
            if($count>0)
                throw new InvalidApi(sprintf('api name %s already exists!',$name));

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
     * @return bool
     */
    public function update($id, array $params){

        $res      = false;
	    $this_var = $this;

	    $this->tx_service->transaction(function () use ($id,$params, &$res, &$this_var) {

            $api = Api::find($id);
            if(is_null($api))
                throw new InvalidApi(sprintf('api id %s does not exists!',$id));

            $allowed_update_params = array('name','description','active');
            foreach($allowed_update_params as $param){
                if(array_key_exists($param,$params)){

                    if($param=='name'){
                        if(Api::where('name','=',$params[$param])->where('id','<>',$id)->where('resource_server_id','=',$api->resource_server_id)->count()>0)
                            throw new InvalidApi(sprintf('api name %s already exists!',$params[$param]));
                    }

                    $api->{$param} = $params[$param];
                }
            }
            $res = $this_var->save($api);
        });
        return $res;
    }

    /**
     * @param IApi $api
     * @return bool|void
     */
    public function save(IApi $api)
    {
        if(!$api->exists() || count($api->getDirty())>0){
            return $api->Save();
        }
        return true;
    }

    /**
     * @param $id
     * @param $active
     * @return bool
     * @throws InvalidApi
     */
    public function setStatus($id, $active)
    {
        $api = Api::find($id);
        if(is_null($api))
            throw new InvalidApi(sprintf("api id %s does not exists!",$id));
        return $api->update(array('active'=>$active));
    }

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr=1,$page_size=10,array $filters=array(), array $fields=array('*')){
        return Api::Filter($filters)->paginate($page_size,$fields, $pageName ='Page', $page_nbr);
    }
}