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
        $http_response->header('Cache-Control','no-cache, no-store, max-age=0, must-revalidate');
        $http_response->header('Pragma','no-cache');
        $http_response->header('X-content-type-options','nosniff');
        $http_response->header('X-xss-protection','1; mode=block');
        $http_response->header('X-frame-options','SAMEORIGIN');
        return $http_response;
    }
}