<?php

namespace services\oauth2;

use oauth2\models\IApiEndpoint;
use oauth2\services\IApiEndpointService;
use ApiEndpoint;

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
}