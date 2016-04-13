<?php namespace OAuth2\Responses;
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

use OAuth2\OAuth2Protocol;
use Utils\Http\HttpContentType;

/**
 * Class OAuth2AccessTokenResponse
 * @see http://tools.ietf.org/html/rfc6749#section-4.1.4
 * @package OAuth2\Responses
 */
class OAuth2AccessTokenResponse extends OAuth2DirectResponse
{

    /**
     * @param string $access_token
     * @param string $expires_in
     * @param null|string $refresh_token
     * @param null|string $scope
     */
    public function __construct($access_token = null, $expires_in = null, $refresh_token = null, $scope = null)
    {
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct(self::HttpOkResponse, HttpContentType::Json);

        if(!empty($access_token))
        {
            $this[OAuth2Protocol::OAuth2Protocol_AccessToken] = $access_token;
            $this[OAuth2Protocol::OAuth2Protocol_AccessToken_ExpiresIn] = $expires_in;
            $this[OAuth2Protocol::OAuth2Protocol_TokenType] = 'Bearer';
        }

        if(!is_null($refresh_token) && !empty($refresh_token))
            $this[OAuth2Protocol::OAuth2Protocol_RefreshToken] = $refresh_token;

        if(!is_null($scope) && !empty($scope))
            $this[OAuth2Protocol::OAuth2Protocol_Scope] = $scope;
    }
} 