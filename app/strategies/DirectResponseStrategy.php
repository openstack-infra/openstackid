<?php

namespace strategies;

use utils\IHttpResponseStrategy;
use Response;

class DirectResponseStrategy implements IHttpResponseStrategy
{

    public function handle($response)
    {
        $http_response = Response::make($response->getContent(), $response->getHttpCode());
        $http_response->header('Content-Type', $response->getContentType());
        $http_response->header('Cache-Control','no-store');
        $http_response->header('Pragma','no-cache');
        return $http_response;
    }
}