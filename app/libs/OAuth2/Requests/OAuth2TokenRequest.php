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
 * Class OAuth2TokenRequest
 * Base Token Request
 * @package oauth2\requests
 */
class OAuth2TokenRequest extends OAuth2Request
{

    /**
     * OAuth2TokenRequest constructor.
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

        $grant_type = $this->getGrantType();

        if(is_null($grant_type)) {
            $this->last_validation_error = 'grant_type not set';
            return false;
        }

        return true;
    }

    /**
     * @return null|string
     */
    public function getGrantType()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_GrantType);
    }
} 