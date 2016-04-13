<?php namespace OAuth2\Strategies;

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

use OAuth2\Requests\OAuth2AuthenticationRequest;
use OAuth2\Requests\OAuth2Request;
use OAuth2\Responses\OAuth2DirectResponse;
use OAuth2\Responses\OAuth2IndirectFragmentResponse;
use OAuth2\Responses\OAuth2IndirectResponse;
use OAuth2\Responses\OAuth2PostResponse;
use OAuth2\Responses\OAuth2Response;
use Utils\IHttpResponseStrategy;
use Utils\Services\ServiceLocator;
use OAuth2\OAuth2Protocol;
use Exception;

/**
 * Class OAuth2ResponseStrategyFactoryMethod
 * @package OAuth2\Strategies
 */
final class OAuth2ResponseStrategyFactoryMethod
{

    /**
     * @param OAuth2Request $request
     * @param OAuth2Response $response
     * @return IHttpResponseStrategy
     * @throws Exception
     */
    public static function buildStrategy(OAuth2Request $request, OAuth2Response $response)
    {
        $type = $response->getType();

        if($request instanceof OAuth2AuthenticationRequest)
        {
            $response_mode = $request->getResponseMode();

            if(is_null($response_mode))
            {
                $response_mode = OAuth2Protocol::getDefaultResponseMode($request->getResponseType(false));
            }

            switch($response_mode)
            {
                case OAuth2Protocol::OAuth2Protocol_ResponseMode_Fragment:
                    $type = OAuth2IndirectFragmentResponse::OAuth2IndirectFragmentResponse;
                    break;
                case OAuth2Protocol::OAuth2Protocol_ResponseMode_Query:
                    $type = OAuth2IndirectResponse::OAuth2IndirectResponse;
                    break;
                case OAuth2Protocol::OAuth2Protocol_ResponseMode_FormPost:
                    $type = OAuth2PostResponse::OAuth2PostResponse;
                    break;
                case OAuth2Protocol::OAuth2Protocol_ResponseMode_Direct:
                    $type = OAuth2DirectResponse::OAuth2DirectResponse;
                    break;
            }
        }

        switch ($type)
        {
            case OAuth2PostResponse::OAuth2PostResponse:
            {
                return ServiceLocator::getInstance()->getService(OAuth2PostResponse::OAuth2PostResponse);
            }
            break;
            case OAuth2IndirectResponse::OAuth2IndirectResponse:
            {
                return ServiceLocator::getInstance()->getService(OAuth2IndirectResponse::OAuth2IndirectResponse);
            }
            break;

            case OAuth2IndirectFragmentResponse::OAuth2IndirectFragmentResponse:
            {
                return ServiceLocator::getInstance()->getService(OAuth2IndirectFragmentResponse::OAuth2IndirectFragmentResponse);
            }
            break;
            case OAuth2DirectResponse::OAuth2DirectResponse:
            {
                return ServiceLocator::getInstance()->getService(OAuth2DirectResponse::OAuth2DirectResponse);
            }
            break;
            default:
                throw new Exception(sprintf("Invalid OAuth2 response Type %s", $type));
            break;
        }
    }
} 