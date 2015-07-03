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

namespace oauth2\responses;

use oauth2\OAuth2Protocol;

/**
 * Class OAuth2IDTokenFragmentResponse
 * @package oauth2\responses
 */
class OAuth2IDTokenFragmentResponse extends OAuth2AccessTokenFragmentResponse
{
    /**
     * @param $return_to
     * @param null $access_token
     * @param null $expires_in
     * @param null $scope
     * @param null $state
     * @param null $session_state
     * @param null $id_token
     */
    public function __construct
    (
        $return_to,
        $access_token  = null,
        $expires_in    = null,
        $scope         = null,
        $state         = null,
        $session_state = null,
        $id_token      = null
    )
    {

        parent::__construct($return_to, $access_token, $expires_in, $scope, $state);

        if(!empty($session_state))
            $this[OAuth2Protocol::OAuth2Protocol_Session_State] = $session_state;

        if(!empty($id_token))
            $this[OAuth2Protocol::OAuth2Protocol_IdToken] = $id_token;
    }
}