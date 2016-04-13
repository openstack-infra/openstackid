<?php namespace OAuth2\Responses;

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

use OAuth2\OAuth2Protocol;

/**
 * Class OAuth2IdTokenResponse
 * @package OAuth2\Responses
 */
final class OAuth2IdTokenResponse extends OAuth2AccessTokenResponse
{

    /**
     * @param string      $access_token
     * @param string      $expires_in
     * @param string      $id_token
     * @param null|string $refresh_token
     * @param null|string $scope
     */
    public function __construct
    (
        $access_token  = null,
        $expires_in    = null,
        $id_token      = null,
        $refresh_token = null,
        $scope         = null
    )
    {
        parent::__construct($access_token, $expires_in, $refresh_token, $scope);

        if(!empty($id_token))
            $this[OAuth2Protocol::OAuth2Protocol_IdToken] = $id_token;
    }
}