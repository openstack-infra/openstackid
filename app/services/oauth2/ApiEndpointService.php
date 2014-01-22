<?php

namespace services\oauth2;

use oauth2\models\IApiEndpoint;
use oauth2\services\IApiEndpointService;
use ApiEndpoint;
use DB;

/**
 * Class ApiEndpointService
 * @package services\oauth2
 */
class ApiEndpointService implements IApiEndpointService {

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
     * @param $id
     * @return IApiEndpoint
     */
    public function get($id){
        return ApiEndpoint::find($id);
    }

    /**
     * @param int $page_size
     * @param int $page_nbr
     * @return mixed
     */
    public function getAll($page_size=10,$page_nbr=1){
        DB::getPaginator()->setCurrentPage($page_nbr);
        return ApiEndpoint::paginate($page_size);
    }

}