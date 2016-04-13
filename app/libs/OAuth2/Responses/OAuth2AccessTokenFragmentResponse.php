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
/**
 * Class OAuth2AccessTokenFragmentResponse
 * @package OAuth2\Responses
 */
class OAuth2AccessTokenFragmentResponse extends OAuth2IndirectFragmentResponse
{

    /**
     * @param string $return_to
     * @param string $access_token
     * @param int $expires_in
     * @param null|string $scope
     * @param null|string $state
     */
    public function __construct($return_to, $access_token, $expires_in, $scope = null, $state = null)
    {

        parent::__construct();

        $this->setReturnTo($return_to);

        if(!is_null($access_token) && !empty($access_token))
        {
            $this[OAuth2Protocol::OAuth2Protocol_AccessToken] = $access_token;
            $this[OAuth2Protocol::OAuth2Protocol_AccessToken_ExpiresIn] = $expires_in;
            $this[OAuth2Protocol::OAuth2Protocol_TokenType] = 'Bearer';
        }

        if(!is_null($scope) && !empty($scope))
            $this[OAuth2Protocol::OAuth2Protocol_Scope] = $scope;

        if(!is_null($state) && !empty($state))
            $this[OAuth2Protocol::OAuth2Protocol_State] = $state;
    }
} 