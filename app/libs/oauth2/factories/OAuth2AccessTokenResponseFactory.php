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
use oauth2\models\AccessToken;
use oauth2\models\AuthorizationCode;
use oauth2\models\RefreshToken;
use oauth2\OAuth2Protocol;
use oauth2\requests\OAuth2AccessTokenRequestAuthCode;
use oauth2\requests\OAuth2Request;
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
     * @param OAuth2AccessTokenRequestAuthCode $request
     * @return null|OAuth2AccessTokenResponse
     */
    static public function build
    (
        ITokenService $token_service,
        AuthorizationCode $auth_code,
        OAuth2AccessTokenRequestAuthCode $request
    )
    {
        $response = null;

        if(self::authCodewasIssuedForOIDC($auth_code))
        {

           $access_token  = null;
           $id_token      = null;
           $refresh_token = null;
           $response_type = explode
           (
               OAuth2Protocol::OAuth2Protocol_ResponseType_Delimiter,
               $auth_code->getResponseType()
           );

           $is_hybrid_flow = OAuth2Protocol::responseTypeBelongsToFlow
           (
               $response_type,
               OAuth2Protocol::OAuth2Protocol_GrantType_Hybrid
           );

           if($is_hybrid_flow)
           {

               if(in_array(OAuth2Protocol::OAuth2Protocol_ResponseType_Token, $response_type))
               {

                   $access_token = $token_service->createAccessToken($auth_code, $request->getRedirectUri());
               }

               // check if should emmit id token

               if(in_array(OAuth2Protocol::OAuth2Protocol_ResponseType_IdToken, $response_type))
               {

                   $id_token     = $token_service->createIdToken
                   (
                       $auth_code->getNonce(),
                       $auth_code->getClientId(),
                       $access_token
                   );
               }

               if(is_null($id_token) && is_null($access_token)) throw new InvalidOAuth2Request;
           }
           else
           {
               $access_token = $token_service->createAccessToken($auth_code, $request->getRedirectUri());

               $id_token     = $token_service->createIdToken
               (
                   $auth_code->getNonce(),
                   $auth_code->getClientId(),
                   $access_token
               );
           }

           if(!is_null($access_token))
                $refresh_token = $access_token->getRefreshToken();

            $response = new OAuth2IdTokenResponse
            (
                is_null($access_token)  ? null : $access_token->getValue(),
                is_null($access_token)  ? null : $access_token->getLifetime(),
                is_null($id_token)      ? null : $id_token->toCompactSerialization(),
                is_null($refresh_token) ? null : $refresh_token->getValue()
            );
        }
        else // normal oauth2.0 code flow
        {
            $access_token  = $token_service->createAccessToken($auth_code, $request->getRedirectUri());
            $refresh_token = $access_token->getRefreshToken();

            $response = new OAuth2AccessTokenResponse
            (
                $access_token->getValue(),
                $access_token->getLifetime(),
                is_null($refresh_token) ? null : $refresh_token->getValue()
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