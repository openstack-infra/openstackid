<?php

namespace oauth2\strategies;

use oauth2\responses\OAuth2IndirectResponse;
use oauth2\responses\OAuth2Response;
use utils\services\Registry;

class OAuth2ResponseStrategyFactoryMethod {

    public static function buildStrategy(OAuth2Response $response)
    {
        $type = $response->getType();
        switch ($type) {
            case OAuth2IndirectResponse::OpenIdIndirectResponse:
            {
                return Registry::getInstance()->get(OAuth2IndirectResponse::OpenIdIndirectResponse);
            }
            break;

            default:
                throw new \Exception("Invalid OpenId response Type");
                break;
        }
    }
} 