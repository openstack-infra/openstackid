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

use Exception;
use OAuth2\Exceptions\UnsupportedResponseTypeException;
use OAuth2\Requests\OAuth2Request;
use OAuth2\Responses\OAuth2IndirectErrorResponse;
use OAuth2\Responses\OAuth2IndirectFragmentErrorResponse;
use OAuth2\OAuth2Protocol;
use OAuth2\Responses\OAuth2Response;
use OAuth2\Requests\OAuth2AuthorizationRequest;

/**
 * Class OAuth2IndirectErrorResponseFactoryMethod
 * @package OAuth2\Strategies
 */
final class OAuth2IndirectErrorResponseFactoryMethod
{

    /**
     * @param OAuth2Request $request
     * @param string $error
     * @param string $error_description
     * @param string|null $return_url
     * @return null|OAuth2Response
     * @throws Exception
     */
    public static function buildResponse(OAuth2Request $request = null, $error, $error_description, $return_url = null)
    {

        if($request instanceof OAuth2AuthorizationRequest)
        {
            $response_type = $request->getResponseType(false);

            if (OAuth2Protocol::responseTypeBelongsToFlow($response_type, OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode))
            {
                return new OAuth2IndirectErrorResponse
                (
                    $error,
                    $error_description,
                    $return_url,
                    $request->getState()
                );
            }
            if
            (
                OAuth2Protocol::responseTypeBelongsToFlow($response_type, OAuth2Protocol::OAuth2Protocol_GrantType_Implicit) ||
                OAuth2Protocol::responseTypeBelongsToFlow($response_type, OAuth2Protocol::OAuth2Protocol_GrantType_Hybrid)
            )
            {
                return new OAuth2IndirectFragmentErrorResponse
                (
                    $error,
                    $error_description,
                    $return_url,
                    $request->getState()
                );
            }

            throw new UnsupportedResponseTypeException
            (
                sprintf
                (
                    "invalid response type %s",
                    $request->getResponseType()
                )
            );
        }
    }
} 