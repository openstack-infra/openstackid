<?php namespace OpenId\Strategies;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use OpenId\Responses\OpenIdDirectResponse;
use OpenId\Responses\OpenIdIndirectResponse;
use OpenId\Responses\OpenIdResponse;
use Utils\IHttpResponseStrategy;
use Utils\Services\ServiceLocator;
use Exception;
/**
 * Class OpenIdResponseStrategyFactoryMethod
 * @package OpenId\Strategies
 */
final class OpenIdResponseStrategyFactoryMethod
{
    /**
     * @param OpenIdResponse $response
     * @return IHttpResponseStrategy
     * @throws Exception
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