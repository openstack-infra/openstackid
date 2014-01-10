<?php

namespace oauth2\services;

use oauth2\models\IApi;


interface IApiService {
    /**
     * @param $url
     * @param $http_method
     * @return IApi
     */
    public function getApiByUrlAndMethod($url,$http_method);
} 