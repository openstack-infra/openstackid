<?php
/**
 * Copyright 2015 OpenStack Foundation
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

namespace oauth2\factories;

use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\OAuth2Protocol;
use oauth2\requests\OAuth2AuthenticationRequest;
use oauth2\requests\OAuth2AuthorizationRequest;
use oauth2\responses\OAuth2AccessTokenFragmentResponse;
use oauth2\responses\OAuth2IDTokenFragmentResponse;
use oauth2\services\ITokenService;
use openid\model\IOpenIdUser;

/**
 * Class OAuth2AccessTokenFragmentResponseFactory
 * @package oauth2\factories
 */
final class OAuth2AccessTokenFragmentResponseFactory
{
    /**
     * @param OAuth2AuthorizationRequest $request
     * @param string $audience
     * @param IOpenIdUser $user
     * @param ITokenService $token_service
     * @return OAuth2AccessTokenFragmentResponse|OAuth2IDTokenFragmentResponse
     * @throws InvalidOAuth2Request
     */
    static public function build
    (
        OAuth2AuthorizationRequest $request,
        $audience,
        IOpenIdUser $user,
        ITokenService $token_service
    )
    {
        if($request instanceof OAuth2AuthenticationRequest)
        {
            $access_token = null;

            if(str_contains($request->getResponseType(), OAuth2Protocol::OAuth2Protocol_ResponseType_Token))
            {
                $access_token = $token_service->createAccessTokenFromParams
                (
                    $request->getClientId(),
                    $request->getScope(),
                    $audience,
                    $user->getId()
                );
            }

            $id_token = $token_service->createIdToken
            (
                $request->getNonce(),
                $request->getClientId(),
                $access_token
            );

            return new OAuth2IDTokenFragmentResponse
            (
                $request->getRedirectUri(),
                is_null($access_token) ? null : $access_token->getValue(),
                is_null($access_token) ? null : $access_token->getLifetime(),
                is_null($access_token) ? null : $request->getScope(),
                $request->getState(),
                $id_token->toCompactSerialization()
            );
        }

        if($request instanceof OAuth2AuthorizationRequest)
        {
            $access_token = $token_service->createAccessTokenFromParams
            (
                $request->getClientId(),
                $request->getScope(),
                $audience,
                $user->getId()
            );

            return new OAuth2AccessTokenFragmentResponse
            (
                $request->getRedirectUri(),
                $access_token->getValue(),
                $access_token->getLifetime(),
                $request->getScope(),
                $request->getState()
            );
        }

        throw new InvalidOAuth2Request;
    }
}