<?php

namespace oauth2\strategies;

use oauth2\responses\OAuth2DirectResponse;
use oauth2\responses\OAuth2IndirectResponse;
use oauth2\responses\OAuth2Response;
use utils\services\Registry;

class OAuth2ResponseStrategyFactoryMethod {

    public static function buildStrategy(OAuth2Response $response)
    {
        $type = $response->getType();
        switch ($type) {
            case OAuth2IndirectResponse::OAuth2IndirectResponse:
            {
                return Registry::getInstance()->get(OAuth2IndirectResponse::OAuth2IndirectResponse);
            }
            break;
            case OAuth2DirectResponse::OAuth2DirectResponse:
            {
                return Registry::getInstance()->get(OAuth2DirectResponse::OAuth2DirectResponse);
            }
            break;
            default:
                throw new \Exception("Invalid OAuth2 response Type");
                break;
        }
    }
} 