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

class OpenIdResponseStrategyFactoryMethod {
    /**
     * @param OpenIdResponse $response
     * @return IOpenIdResponseStrategy
     */
    public static function buildStrategy(OpenIdResponse $response){
        return null;
    }
}