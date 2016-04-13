<?php namespace OAuth2\Models;
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
use OAuth2\Exceptions\InvalidTokenEndpointAuthMethodException;
use OAuth2\OAuth2Protocol;
/**
 * Class ClientAuthenticationContext
 * @package OAuth2\Models
 */
abstract class ClientAuthenticationContext
{
    /**
     * @var string
     */
    protected $auth_type;

    /**
     * @var string
     */
    protected $client_id;

    /**
     * @var IClient
     */
    protected $client;

    /**
     * @param string $client_id
     * @param string $auth_type
     * @throws InvalidTokenEndpointAuthMethodException
     */
    public function __construct($client_id, $auth_type)
    {
        if( !in_array($auth_type, OAuth2Protocol::$token_endpoint_auth_methods))
            throw new InvalidTokenEndpointAuthMethodException($auth_type);

        $this->client_id = $client_id;
        $this->auth_type = $auth_type;
    }

    /**
     * @return string
     */
    public function getAuthType()
    {
        return $this->auth_type;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->client_id;
    }

    /**
     * @param IClient $client
     * @return $this
     */
    public function setClient(IClient $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return IClient
     */
    public function getClient()
    {
        return $this->client;
    }

}