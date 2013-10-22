<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 2:36 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\strategies;
use openid\responses\OpenIdResponse;
use openid\responses\OpenIdDirectResponse;
use openid\responses\OpenIdIndirectResponse;
use openid\services\Registry;

class OpenIdResponseStrategyFactoryMethod {
    /**
     * @param OpenIdResponse $response
     * @throws \Exception
     */
    public static function buildStrategy(OpenIdResponse $response){
        $type = $response->getType();
        switch($type)
        {
            case OpenIdIndirectResponse::OpenIdIndirectResponse:
            {
                return Registry::getInstance()->get(OpenIdIndirectResponse::OpenIdIndirectResponse);
            }
            break;
            case OpenIdDirectResponse::OpenIdDirectResponse:
            {
                return Registry::getInstance()->get(OpenIdIndirectResponse::OpenIdDirectResponse);
            }
            break;
            default:
                throw new \Exception("Invalid OpenId response Type");
            break;
        }
    }
}