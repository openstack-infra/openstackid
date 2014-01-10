<?php
namespace services\oauth2;

use oauth2\models\IApi;
use oauth2\services\IApiService;
use Api;

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
}