<?php

namespace services\oauth2;

use oauth2\models\IApiEndpoint;
use oauth2\services\IApiEndpointService;
use ApiEndpoint;
use ApiScope;
use DB;
use  oauth2\exceptions\InvalidApiEndpoint;
use  oauth2\exceptions\InvalidApiScope;
use utils\db\ITransactionService;
/**
 * Class ApiEndpointService
 * @package services\oauth2
 */
class ApiEndpointService implements IApiEndpointService {

	private $tx_service;

	/**
	 * @param ITransactionService $tx_service
	 */
	public function __construct(ITransactionService $tx_service){
		$this->tx_service = $tx_service;
	}

    /**
     * @param $url
     * @param $http_method
     * @return IApiEndpoint
     */
    public function getApiEndpointByUrlAndMethod($url, $http_method)
    {
        return ApiEndpoint::where('route','=',$url)->where('http_method','=',$http_method)->first();
    }

    /**
     * @param $url
     * @return IApiEndpoint
     */
    public function getApiEndpointByUrl($url)
    {
        return ApiEndpoint::where('route','=',$url)->first();
    }


    /**
     * @param $id
     * @return IApiEndpoint
     */
    public function get($id){
        return ApiEndpoint::find($id);
    }

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr=1,$page_size=10,array $filters=array(), array $fields=array('*')){
        DB::getPaginator()->setCurrentPage($page_nbr);
        return ApiEndpoint::Filter($filters)->paginate($page_size,$fields);
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
        $instance = null;

	    $this->tx_service->transaction(function () use ($name, $description, $active,$allow_cors, $route, $http_method, $api_id, $rate_limit, &$instance) {

            //check that does not exists an endpoint with same http method and same route
            if(ApiEndpoint::where('http_method','=',$http_method)->where('route','=',$route)->where('api_id','=',$api_id)->count()>0)
                throw new InvalidApiEndpoint(sprintf('there is already an endpoint api with route %s and http method %s',$route,$http_method));

            $instance = new ApiEndpoint(
                array(
                    'name'               => $name,
                    'description'        => $description,
                    'active'             => $active,
                    'route'              => $route,
                    'http_method'        => $http_method,
                    'api_id'             => $api_id,
                    'allow_cors'         => $allow_cors,
                    'rate_limit'         => (int)$rate_limit,
                )
            );
            $instance->Save();
        });
        return $instance;
    }

    /**
     * @param int $id
     * @param array $params
     * @return bool
     * @throws \oauth2\exceptions\InvalidApiEndpoint
     */
    public function update($id, array $params){

        $res = false;
	    $this_var = $this;

	    $this->tx_service->transaction(function () use ($id,$params, &$res,&$this_var){
            $endpoint = ApiEndpoint::find($id);
            if(is_null($endpoint))
                throw new InvalidApiEndpoint(sprintf('api endpoint id %s does not exists!',$id));

            $allowed_update_params = array('name','description','active','route','http_method','allow_cors', 'rate_limit');
            foreach($allowed_update_params as $param){
                if(array_key_exists($param,$params)){
                    $endpoint->{$param} = $params[$param];
                }
            }
            //check that does not exists an endpoint with same http method and same route
            if(ApiEndpoint::where('http_method', '=' , $endpoint->http_method)->where('route', '=', $endpoint->route)->where('id', '<>' ,$endpoint->id)->where('api_id','=',$endpoint->api_id)->count()>0)
                throw new InvalidApiEndpoint(sprintf('there is already an endpoint api with route %s and http method %s',$endpoint->route,$endpoint->http_method));
            $res = $this_var->save($endpoint);
        });
        return $res;
    }

    /**
     * Adds a new required scope to a given api endpoint,
     * given scope must belongs to owner api of the given endpoint
     * @param int $api_endpoint_id
     * @param int $scope_id
     * @return boolean
     * @throws \oauth2\exceptions\InvalidApiScope
     * @throws \oauth2\exceptions\InvalidApiEndpoint
     */
    public function addRequiredScope($api_endpoint_id, $scope_id)
    {
        $res = false;

	    $this->tx_service->transaction(function () use($api_endpoint_id, $scope_id,&$res){

            $api_endpoint = ApiEndpoint::find($api_endpoint_id);

            if(is_null($api_endpoint))
                throw new InvalidApiEndpoint(sprintf("api endpoint id %s does not exists!.",$api_endpoint_id));

            $scope        = ApiScope::find($scope_id);

            if(is_null($scope))
                throw new InvalidApiScope(sprintf("api scope id %s does not exists!.",$scope_id));

            if($scope->api_id!==$api_endpoint->api_id)
                throw new InvalidApiScope(sprintf("api scope id %s does not belong to api id %s !.",$scope_id,$api_endpoint->api_id));

            $res = $api_endpoint->scopes()->where('id','=',$scope_id)->count();

            if($res>0)
                throw new InvalidApiScope(sprintf("api scope id %s already belongs to endpoint id %s!.",$scope_id,$api_endpoint->id));

            $api_endpoint->scopes()->attach($scope_id);

            $res = true;
        });
        return $res;
    }


    /**
     * Removes a required scope to a given api endpoint,
     * given scope must belongs to owner api of the given endpoint
     * @param int $api_endpoint_id
     * @param int $scope_id
     * @return boolean
     * @throws \oauth2\exceptions\InvalidApiScope
     * @throws \oauth2\exceptions\InvalidApiEndpoint
     */
    public function removeRequiredScope($api_endpoint_id, $scope_id)
    {

        $res = false;

	    $this->tx_service->transaction(function () use($api_endpoint_id, $scope_id,&$res){

            $api_endpoint = ApiEndpoint::find($api_endpoint_id);

            if(is_null($api_endpoint))
                throw new InvalidApiEndpoint(sprintf("api endpoint id %s does not exists!.",$api_endpoint_id));

            $scope        = ApiScope::find($scope_id);

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
     */
    public function delete($id)
    {
        $res = false;
	    $this->tx_service->transaction(function () use ($id,&$res) {
            $endpoint = ApiEndpoint::find($id);
            if(!is_null($endpoint)){
                $res = $endpoint->delete();
            }
        });
        return $res;
    }

    public function save(IApiEndpoint $api_endpoint)
    {
        if(!$api_endpoint->exists() || count($api_endpoint->getDirty())>0){
            return $api_endpoint->Save();
        }
        return true;
    }

    /**
     * @param int $id
     * @param boolean $active
     * @return boolean
     */
    public function setStatus($id, $active)
    {
	    $endpoint = ApiEndpoint::find($id);
        if(is_null($endpoint)) return false;
        return $endpoint->update(array('active'=>$active));
    }
}