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
use OAuth2\Models\IApiEndpoint;
use OAuth2\Repositories\IApiEndpointRepository;
use OAuth2\Repositories\IApiScopeRepository;
use OAuth2\Services\IApiEndpointService;
use Models\OAuth2\ApiEndpoint;
use OAuth2\Exceptions\InvalidApiEndpoint;
use OAuth2\Exceptions\InvalidApiScope;
use Utils\Db\ITransactionService;
use Utils\Exceptions\EntityNotFoundException;
/**
 * Class ApiEndpointService
 * @package Services\OAuth2
 */
final class ApiEndpointService implements IApiEndpointService {

    /**
     * @var ITransactionService
     */
	private $tx_service;

    /**
     * @var IApiEndpointRepository
     */
    private $repository;

    /**
     * @var IApiScopeRepository
     */
    private $scope_repository;

    /**
     * ApiEndpointService constructor.
     * @param ITransactionService $tx_service
     * @param IApiEndpointRepository $repository
     * @param IApiScopeRepository $scope_repository
     */
	public function __construct
    (
        ITransactionService $tx_service,
        IApiEndpointRepository $repository,
        IApiScopeRepository $scope_repository
    ){
		$this->tx_service       = $tx_service;
        $this->repository       = $repository;
        $this->scope_repository = $scope_repository;
	}


    /**
     * Adds a new api endpoint to an existent api
     * @param string $name
     * @param string $description
     * @param boolean $active
     * @param boolean $allow_cors
     * @param string $route
     * @param string $http_method
     * @param integer $api_id
     * @param integer $rate_limit
     * @return IApiEndpoint
     */
    public function add($name, $description, $active,$allow_cors, $route, $http_method, $api_id, $rate_limit)
    {
        return $this->tx_service->transaction(function () use ($name, $description, $active, $allow_cors, $route, $http_method, $api_id, $rate_limit) {

            //check that does not exists an endpoint with same http method and same route

            $former_endpoint = $this->repository->getApiEndpointByUrlAndMethodAndApi($route, $http_method, $api_id);

            if(!is_null($former_endpoint))
                throw new InvalidApiEndpoint
                (
                    sprintf
                    (
                        'there is already an endpoint api with route %s and http method %s',
                        $route,
                        $http_method
                    )
                );
            // todo: move to factory
            $instance = new ApiEndpoint(
                [
                    'name'        => $name,
                    'description' => $description,
                    'active'      => $active,
                    'route'       => $route,
                    'http_method' => $http_method,
                    'api_id'      => $api_id,
                    'allow_cors'  => $allow_cors,
                    'rate_limit'  => (int)$rate_limit,
                ]
            );

            $this->repository->add($instance);

            return $instance;
        });

    }

    /**
     * @param int $id
     * @param array $params
     * @return bool
     * @throws EntityNotFoundException
     * @throws InvalidApiEndpoint
     */
    public function update($id, array $params){

	    return $this->tx_service->transaction(function () use ($id, $params){
            $endpoint = $this->repository->get($id);

            if(is_null($endpoint))
                throw new EntityNotFoundException(sprintf('api endpoint id %s does not exists!', $id));

            $allowed_update_params = ['name','description','active','route','http_method','allow_cors', 'rate_limit'];

            foreach($allowed_update_params as $param){
                if(array_key_exists($param,$params)){
                    $endpoint->{$param} = $params[$param];
                }
            }

            //check that does not exists an endpoint with same http method and same route
            $former_endpoint = $this->repository->getApiEndpointByUrlAndMethodAndApi
            (
                $endpoint->route,
                $endpoint->http_method,
                $endpoint->api_id
            );

            if(!is_null($former_endpoint) && $former_endpoint->id != $endpoint->id)
                throw new InvalidApiEndpoint
                (
                    sprintf
                    (
                        'there is already an endpoint api with route %s and http method %s',
                        $endpoint->route,
                        $endpoint->http_method
                    )
                );

            return true;
        });
    }

    /**
     * Adds a new required scope to a given api endpoint,
     * given scope must belongs to owner api of the given endpoint
     * @param int $api_endpoint_id
     * @param int $scope_id
     * @return boolean
     * @throws InvalidApiScope
     * @throws EntityNotFoundException
     * @throws InvalidApiEndpoint
     */
    public function addRequiredScope($api_endpoint_id, $scope_id)
    {

	    return $this->tx_service->transaction(function () use($api_endpoint_id, $scope_id){

            $api_endpoint = $this->repository->get($api_endpoint_id);

            if(is_null($api_endpoint))
                throw new EntityNotFoundException(sprintf("api endpoint id %s does not exists!.",$api_endpoint_id));

            $scope        = $this->scope_repository->get($scope_id);

            if(is_null($scope))
                throw new InvalidApiScope(sprintf("api scope id %s does not exists!.", $scope_id));

            if($scope->api_id != $api_endpoint->api_id)
                throw new InvalidApiScope(sprintf("api scope id %s does not belong to api id %s !.",$scope_id,$api_endpoint->api_id));

            $res = $api_endpoint->scopes()->where('id', '=' , $scope_id)->count();

            if($res > 0)
                throw new InvalidApiScope(sprintf("api scope id %s already belongs to endpoint id %s!.",$scope_id,$api_endpoint->id));

            $api_endpoint->scopes()->attach($scope_id);

            return true;
        });
    }


    /**
     * Removes a required scope to a given api endpoint,
     * given scope must belongs to owner api of the given endpoint
     * @param int $api_endpoint_id
     * @param int $scope_id
     * @return boolean
     * @throws InvalidApiScope
     * @throws InvalidApiEndpoint
     * @throws EntityNotFoundException
     */
    public function removeRequiredScope($api_endpoint_id, $scope_id)
    {

        $res = false;

	    $this->tx_service->transaction(function () use($api_endpoint_id, $scope_id,&$res){

            $api_endpoint = $this->repository->get($api_endpoint_id);

            if(is_null($api_endpoint))
                throw new EntityNotFoundException(sprintf("api endpoint id %s does not exists!.",$api_endpoint_id));

            $scope        = $this->scope_repository->get($scope_id);

            if(is_null($scope))
                throw new InvalidApiScope(sprintf("api scope id %s does not exists!.",$scope_id));

            if($scope->api_id !== $api_endpoint->api_id)
                throw new InvalidApiScope(sprintf("api scope id %s does not belongs to api id %s!.",$scope_id,$api_endpoint->api_id));

            $res = $api_endpoint->scopes()->where('id','=',$scope_id)->count();

            if($res==0)
                throw new InvalidApiScope(sprintf("api scope id %s does not belongs to endpoint id %s !.",$scope_id,$api_endpoint->id));

            $api_endpoint->scopes()->detach($scope_id);

            $res = true;
        });
        return $res;
    }

    /**
     * deletes a given api endpoint
     * @param int $id
     * @return boolean
     * @throws EntityNotFoundException
     */
    public function delete($id)
    {
	    return $this->tx_service->transaction(function () use ($id) {
            $endpoint = $this->repository->get($id);
            if(is_null($endpoint)) throw new EntityNotFoundException();
            $this->repository->delete($endpoint);
            return true;
        });
    }

    /**
     * @param int $id
     * @param boolean $active
     * @return boolean
     * @throws EntityNotFoundException
     */
    public function setStatus($id, $active)
    {
	    return $this->tx_service->transaction(function () use ($id, $active) {
            $endpoint = $this->repository->get($id);
            if(is_null($endpoint)) throw new EntityNotFoundException();
            $endpoint->active = $active;
            $this->repository->add($endpoint);
            return true;
        });
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->tx_service->transaction(function () use($id){
           return $this->repository->get($id);
        });
    }
}