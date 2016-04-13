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
 * Class OAuth2AccessTokenRequestClientCredentials
 * @see http://tools.ietf.org/html/rfc6749#section-4.4.2
 * @package OAuth2\Requests
 */
class OAuth2AccessTokenRequestClientCredentials extends OAuth2TokenRequest {

    /**
     * OAuth2AccessTokenRequestClientCredentials constructor.
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

        return true;
    }

    /**
     * @return null|string
     */
    public function getScope()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_Scope);
    }
} 