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
        return $http_response;
    }
}