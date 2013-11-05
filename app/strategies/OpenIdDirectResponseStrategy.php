<?php

namespace strategies;

use openid\strategies\IOpenIdResponseStrategy;
use Response;

class OpenIdDirectResponseStrategy implements IOpenIdResponseStrategy
{

    public function handle($response)
    {
        $http_response = Response::make($response->getContent(), $response->getHttpCode());
        $http_response->header('Content-Type', $response->getContentType());
        return $http_response;
    }
}