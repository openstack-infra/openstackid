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
use OAuth2\Repositories\IApiRepository;
use OAuth2\Services\IApiService;
use OAuth2\Exceptions\InvalidApi;
use Utils\Db\ITransactionService;
use Utils\Exceptions\EntityNotFoundException;

/**
 * Class ApiService
 * @package Services\OAuth2
 */
class ApiService implements IApiService {

    /**
     * @var ITransactionService
     */
	private $tx_service;

    /**
     * @var IApiRepository
     */
    private $repository;

    /**
     * ApiService constructor.
     * @param IApiRepository $repository
     * @param ITransactionService $tx_service
     */
	public function __construct
    (
        IApiRepository $repository,
        ITransactionService $tx_service
    ){
        $this->repository = $repository;
		$this->tx_service = $tx_service;
	}

    /**
     * @param int $id
     * @return bool
     * @throws EntityNotFoundException
     */
    public function delete($id)
    {

	    return $this->tx_service->transaction(function () use ($id) {
            $api = $this->repository->get($id);
            if(is_null($api)) throw new EntityNotFoundException();
            $this->repository->delete($api);
            return true;
        });

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

        if(is_string($active)){
            $active = strtoupper($active) == 'TRUE' ? true : false;
        }

	    return $this->tx_service->transaction(function () use ($name, $description, $active, $resource_server_id ) {

            $former_api = $this->repository->getByNameAndResourceServer($name, $resource_server_id);
            if(!is_null($former_api))
                throw new InvalidApi(sprintf('api name %s already exists!',$name));
            // todo : move to factory
            $instance = new Api(
                [
                    'name'               => $name,
                    'description'        => $description,
                    'active'             => $active,
                    'resource_server_id' => $resource_server_id
                ]
            );

            $this->repository->add($instance);

            return $instance;
        });

    }

    /**
     * @param $id
     * @param array $params
     * @return bool
     * @throws EntityNotFoundException
     * @throws InvalidApi
     */
    public function update($id, array $params){


	    return $this->tx_service->transaction(function () use ($id,$params) {

            $api = $this->repository->get($id);
            if(is_null($api))
                throw new EntityNotFoundException(sprintf('api id %s does not exists!', $id));

            $allowed_update_params = ['name','description','active'];
            foreach($allowed_update_params as $param){
                if(array_key_exists($param,$params)){

                    if($param=='name'){
                        $former_api = $this->repository->getByNameAndResourceServer($params[$param], $api->resource_server_id);
                        if(!is_null($former_api) && $former_api->id != $id )
                            throw new InvalidApi(sprintf('api name %s already exists!', $params[$param]));
                    }

                    $api->{$param} = $params[$param];
                }
            }

            $this->repository->add($api);
            return true;
        });

    }

    /**
     * @param int $id
     * @param bool $active
     * @return bool
     * @throws EntityNotFoundException
     */
    public function setStatus($id, $active)
    {
        return $this->tx_service->transaction(function() use($id, $active){

            $api = $this->repository->get($id);

            if(is_null($api))
                throw new EntityNotFoundException(sprintf('api id %s does not exists!', $id));

            $api->active = $active;

            $this->repository->add($api);

            return true;
        });
    }
}