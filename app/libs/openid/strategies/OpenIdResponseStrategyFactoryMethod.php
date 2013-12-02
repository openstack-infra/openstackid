<?php

namespace openid\strategies;

use openid\responses\OpenIdDirectResponse;
use openid\responses\OpenIdIndirectResponse;
use openid\responses\OpenIdResponse;
use openid\services\OpenIdRegistry;

class OpenIdResponseStrategyFactoryMethod
{
    /**
     * @param OpenIdResponse $response
     * @throws \Exception
     */
    public static function buildStrategy(OpenIdResponse $response)
    {
        $type = $response->getType();
        switch ($type) {
            case OpenIdIndirectResponse::OpenIdIndirectResponse:
            {
                return OpenIdRegistry::getInstance()->get(OpenIdIndirectResponse::OpenIdIndirectResponse);
            }
                break;
            case OpenIdDirectResponse::OpenIdDirectResponse:
            {
                return OpenIdRegistry::getInstance()->get(OpenIdDirectResponse::OpenIdDirectResponse);
            }
                break;
            default:
                throw new \Exception("Invalid OpenId response Type");
                break;
        }
    }
}