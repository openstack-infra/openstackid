<?php namespace OAuth2\Requests;
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

use OAuth2\OAuth2Message;
use OAuth2\OAuth2Protocol;

/**
 * OpenID Connect logout request initiated by the relying party (RP).
 *
 * Class OAuth2LogoutRequest
 * @package OAuth2\Requests
 */
final class OAuth2LogoutRequest extends OAuth2Request
{
    /**
     * OAuth2LogoutRequest constructor.
     * @param OAuth2Message $msg
     */
    public function __construct(OAuth2Message $msg)
    {
        parent::__construct($msg);
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $this->last_validation_error = '';

        $id_token_hint = $this->getIdTokenHint();
        if(empty($id_token_hint))
        {
            $this->last_validation_error = 'id_token_hint not set';
            return false;
        }
        $log_out_uri = $this->getPostLogoutRedirectUri();
        $state       = $this->getState();
        if(!empty($log_out_uri))
        {
            return !empty($state);
        }
        return true;
    }

    /**
     * @return string|null
     */
    public function getIdTokenHint()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_IDTokenHint);
    }

    /**
     * @return string|null
     */
    public function getPostLogoutRedirectUri()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_PostLogoutRedirectUri);
    }

    /**
     * @return string|null
     */
    public function getState()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_State);
    }
}