<?php namespace OAuth2\Exceptions;
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
use Exception;
/**
 * Class OAuth2ClientBaseException
 * @package OAuth2\Exceptions
 */
class OAuth2ClientBaseException extends Exception
{
    /**
     * @var string
     */
    protected $client_id;

    /**
     * OAuth2ClientBaseException constructor.
     * @param string $client_id
     * @param string $message
     */
    public function __construct($client_id, $message = "")
    {
        $this->client_id = $client_id;
        $message = "OAuth2 Client Base Exception : " . $message;
        parent::__construct($message, 0, null);
    }

    /**
     * @return string
     */
    public function getClientId(){
        return $this->client_id;
    }

}