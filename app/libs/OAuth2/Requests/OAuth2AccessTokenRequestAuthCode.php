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

use OAuth2\OAuth2Protocol;
use OAuth2\OAuth2Message;

/**
 * Class OAuth2AccessTokenRequest
 * @see http://tools.ietf.org/html/rfc6749#section-4.1.3
 * @package OAuth2\Requests
 */
class OAuth2AccessTokenRequestAuthCode extends OAuth2TokenRequest
{

    /**
     * OAuth2AccessTokenRequestAuthCode constructor.
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
        if (!parent::isValid())
            return false;

        $redirect_uri = $this->getRedirectUri();

        if (is_null($redirect_uri))
        {
            $this->last_validation_error = 'redirect_uri not set';
            return false;
        }

        return true;
    }

    /**
     * @return null|string
     */
    public function getRedirectUri()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_RedirectUri);
    }

    /**
     * @return null|string
     */
    public function getClientId()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_ClientId);
    }

    /**
     * @return null|string
     */
    public function getCode()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_ResponseType_Code);
    }
}