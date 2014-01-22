<?php

namespace oauth2\services;


interface IApiEndpointService {
    /**
     * @param $url
     * @param $http_method
     * @return IApi
     */
    public function getApiEndpointByUrlAndMethod($url,$http_method);

} 