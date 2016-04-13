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
 * Class OAuth2AuthorizationResponse
 * @see http://tools.ietf.org/html/rfc6749#section-4.1.2
 * @package OAuth2\Responses
 */
class OAuth2AuthorizationResponse extends OAuth2IndirectResponse
{

    /**
     * @param string $return_url
     * @param string $code
     * @param null|string $scope
     * @param null|string $state
     * @param null|string $session_state
     */
    public function __construct($return_url, $code, $scope = null, $state = null, $session_state = null)
    {
        parent::__construct();
        $this[OAuth2Protocol::OAuth2Protocol_ResponseType_Code]  = $code;
        $this->setReturnTo($return_url);

        if(!empty($scope))
            $this[OAuth2Protocol::OAuth2Protocol_Scope] = $scope;

        if(!empty($state))
            $this[OAuth2Protocol::OAuth2Protocol_State] = $state;

        if(!empty($session_state))
            $this[OAuth2Protocol::OAuth2Protocol_Session_State] = $session_state;
    }

    /**
     * @return null|string
     */
    public function getAuthCode()
    {
        return isset($this[OAuth2Protocol::OAuth2Protocol_ResponseType_Code])?$this[OAuth2Protocol::OAuth2Protocol_ResponseType_Code] :null;
    }

    /**
     * @return null|string
     */
    public function getState()
    {
        return isset($this[OAuth2Protocol::OAuth2Protocol_State])?$this[OAuth2Protocol::OAuth2Protocol_State] :null;
    }

    /**
     * @return null|string
     */
    public function getScope()
    {
        return isset($this[OAuth2Protocol::OAuth2Protocol_Scope])?$this[OAuth2Protocol::OAuth2Protocol_Scope] :null;
    }

    /**
     * @return null|string
     */
    public function getSessionState()
    {
        return isset($this[OAuth2Protocol::OAuth2Protocol_Session_State])?$this[OAuth2Protocol::OAuth2Protocol_Session_State] :null;
    }

}