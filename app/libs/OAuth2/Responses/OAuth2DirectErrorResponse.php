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
 * Class OAuth2DirectErrorResponse
 * @package OAuth2\Responses
 */
class OAuth2DirectErrorResponse extends OAuth2DirectResponse
{

    /**
     * @param string $error
     * @param null|string $error_description
     * @param null|string $state
     */
    public function __construct($error, $error_description = null, $state = null)
    {
        // Error Response: A server receiving an invalid request MUST send a
        // response with an HTTP status code of 400.
        parent::__construct(self::HttpErrorResponse, HttpContentType::Json);
        $this->setError($error);

        if(!empty ($error_description))
            $this->setErrorDescription($error_description);

        if(!empty($state))
            $this->setState($state);
    }

    /**
     * @param $error
     * @return $this
     */
    public function setError($error)
    {
        $this[OAuth2Protocol::OAuth2Protocol_Error] = $error;
        return $this;
    }

    /**
     * @param $state
     * @return $this
     */
    public function setState($state)
    {
        $this[OAuth2Protocol::OAuth2Protocol_State] = $state;
        return $this;
    }

    /**
     * @param $error_description
     * @return $this
     */
    public function setErrorDescription($error_description)
    {
        $this[OAuth2Protocol::OAuth2Protocol_ErrorDescription] = $error_description;
        return $this;
    }
} 