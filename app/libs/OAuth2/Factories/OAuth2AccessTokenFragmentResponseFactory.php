<?php namespace OAuth2\Factories;
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
use OAuth2\Exceptions\InvalidOAuth2Request;
use OAuth2\OAuth2Protocol;
use OAuth2\Requests\OAuth2AuthenticationRequest;
use OAuth2\Requests\OAuth2AuthorizationRequest;
use OAuth2\Responses\OAuth2AccessTokenFragmentResponse;
use OAuth2\Responses\OAuth2IDTokenFragmentResponse;
use OAuth2\Services\ITokenService;
use OpenId\Models\IOpenIdUser;
/**
 * Class OAuth2AccessTokenFragmentResponseFactory
 * @package OAuth2\Factories
 */
final class OAuth2AccessTokenFragmentResponseFactory
{
    /**
     * @param OAuth2AuthorizationRequest $request
     * @param string $audience
     * @param string $session_state
     * @param IOpenIdUser $user
     * @param ITokenService $token_service
     * @return OAuth2AccessTokenFragmentResponse|OAuth2IDTokenFragmentResponse
     * @throws InvalidOAuth2Request
     */
    static public function build
    (
        OAuth2AuthorizationRequest $request,
        $audience,
        $session_state,
        IOpenIdUser $user,
        ITokenService $token_service
    )
    {
        if($request instanceof OAuth2AuthenticationRequest)
        {
            $access_token = null;
            $id_token     = null;

            if(in_array(OAuth2Protocol::OAuth2Protocol_ResponseType_Token, $request->getResponseType(false)))
            {
                $access_token = $token_service->createAccessTokenFromParams
                (
                    $request->getClientId(),
                    $request->getScope(),
                    $audience,
                    $user->getId()
                );
            }

            if(in_array(OAuth2Protocol::OAuth2Protocol_ResponseType_IdToken, $request->getResponseType(false))) {

                $id_token = $token_service->createIdToken
                (
                    $request->getNonce(),
                    $request->getClientId(),
                    $access_token
                );
            }

            return new OAuth2IDTokenFragmentResponse
            (
                $request->getRedirectUri(),
                is_null($access_token) ? null : $access_token->getValue(),
                is_null($access_token) ? null : $access_token->getLifetime(),
                is_null($access_token) ? null : $request->getScope(),
                $request->getState(),
                $session_state,
                is_null($id_token) ? null : $id_token->toCompactSerialization()
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