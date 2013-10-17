<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/16/13
 * Time: 4:36 PM
 * To change this template use File | Settings | File Templates.
 */
namespace strategies;
use openid\responses\OpenIdResponse;
use openid\strategies\IOpenIdResponseStrategy;
use \Response;

class OpenIdDirectResponseStrategy implements IOpenIdResponseStrategy {

    public function handle($response)
    {
        $response = Response::make($response->getContent(), $response->getHttpCode());
        $response->header('Content-Type', $response->getContentType());
        return $response;
    }
}