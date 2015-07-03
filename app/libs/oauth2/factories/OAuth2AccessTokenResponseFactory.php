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

use oauth2\models\AccessToken;
use oauth2\models\AuthorizationCode;
use oauth2\models\RefreshToken;
use oauth2\OAuth2Protocol;
use oauth2\responses\OAuth2AccessTokenResponse;
use oauth2\responses\OAuth2IdTokenResponse;
use oauth2\services\ITokenService;

/**
 * Class OAuth2AccessTokenResponseFactory
 * @package oauth2\factories
 */
final class OAuth2AccessTokenResponseFactory
{

    /**
     * @param ITokenService $token_service
     * @param AuthorizationCode $auth_code
     * @param AccessToken $access_token
     * @return null|OAuth2AccessTokenResponse
     */
    static public function build
    (
        ITokenService $token_service,
        AuthorizationCode $auth_code,
        AccessToken $access_token
    )
    {
        $response       = null;
        $refresh_token = $access_token->getRefreshToken();

        if(self::authCodewasIssuedForOIDC($auth_code))
        {
            $id_token = $token_service->createIdToken
            (
                $auth_code->getNonce(),
                $auth_code->getClientId(),
                $access_token
            );

            $response = new OAuth2IdTokenResponse
            (
                $access_token->getValue(),
                $access_token->getLifetime(),
                $id_token->toCompactSerialization(),
                !is_null($refresh_token) ?
                    $refresh_token->getValue() :
                    null
            );
        }
        else
        {
            $response = new OAuth2AccessTokenResponse
            (
                $access_token->getValue(),
                $access_token->getLifetime(),
                !is_null($refresh_token) ?
                    $refresh_token->getValue() :
                    null
            );
        }

        return $response;
    }

    /**
     * @param AuthorizationCode $auth_code
     * @return bool
     */
    static public function authCodewasIssuedForOIDC(AuthorizationCode $auth_code)
    {
        return str_contains($auth_code->getScope(), OAuth2Protocol::OpenIdConnect_Scope);
    }

}