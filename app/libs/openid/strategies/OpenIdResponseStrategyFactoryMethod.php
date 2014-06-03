<?php

namespace openid\strategies;

use openid\responses\OpenIdDirectResponse;
use openid\responses\OpenIdIndirectResponse;
use openid\responses\OpenIdResponse;
use utils\IHttpResponseStrategy;
use utils\services\ServiceLocator;

/**
 * Class OpenIdResponseStrategyFactoryMethod
 * @package openid\strategies
 */
final class OpenIdResponseStrategyFactoryMethod
{
    /**
     * @param OpenIdResponse $response
     * @return IHttpResponseStrategy
     * @throws \Exception
     */
    public static function buildStrategy(OpenIdResponse $response)
    {
        $type = $response->getType();
        switch ($type) {
            case OpenIdIndirectResponse::OpenIdIndirectResponse:
            {
                return ServiceLocator::getInstance()->getService(OpenIdIndirectResponse::OpenIdIndirectResponse);
            }
                break;
            case OpenIdDirectResponse::OpenIdDirectResponse:
            {
                return ServiceLocator::getInstance()->getService(OpenIdDirectResponse::OpenIdDirectResponse);
            }
                break;
            default:
                throw new \Exception("Invalid OpenId response Type");
                break;
        }
    }
}