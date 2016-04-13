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
use Utils\Http\HttpResponse;

/**
 * Class OAuth2IndirectErrorResponse
 * @package OAuth2\Responses
 */
class OAuth2IndirectErrorResponse extends OAuth2IndirectResponse
{

    /**
     * @param string $error
     * @param string $error_description
     * @param null|string $return_to
     * @param null|string $state
     */
    public function __construct($error, $error_description, $return_to = null, $state = null)
    {
        parent::__construct();

        if(!empty($state))
            $this[OAuth2Protocol::OAuth2Protocol_State] = $state;

        if(!empty($error_description))
            $this->setErrorDescription($error_description);

        $this->setHttpCode(HttpResponse::HttpErrorResponse);
        
        $this->setError($error);

        $this->setReturnTo($return_to);
    }

    /**
     * @param string $error
     */
    public function setError($error)
    {
        $this[OAuth2Protocol::OAuth2Protocol_Error] = $error;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this[OAuth2Protocol::OAuth2Protocol_State] = $state;
    }

    /**
     * @param string $error_description
     */
    public function setErrorDescription($error_description)
    {
        $this[OAuth2Protocol::OAuth2Protocol_ErrorDescription] = $error_description;
    }
} 