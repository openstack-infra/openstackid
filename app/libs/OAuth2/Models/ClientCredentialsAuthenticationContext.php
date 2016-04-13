<?php namespace OAuth2\Models;
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
use OAuth2\Exceptions\InvalidTokenEndpointAuthMethodException;
use OAuth2\OAuth2Protocol;
/**
 * Class ClientCredentialsAuthenticationContext
 * @package OAuth2\Models
 */
final class ClientCredentialsAuthenticationContext extends ClientAuthenticationContext
{

    /**
     * @var string
     */
    private $client_secret;

    /**
     * @param string $client_id
     * @param string $client_secret
     * @param string $auth_type
     * @throws InvalidTokenEndpointAuthMethodException
     */
    public function __construct($client_id, $client_secret, $auth_type)
    {

        parent::__construct($client_id, $auth_type);
        if(!in_array($auth_type, array (
            OAuth2Protocol::TokenEndpoint_AuthMethod_ClientSecretBasic,
            OAuth2Protocol::TokenEndpoint_AuthMethod_ClientSecretPost
        )))
            throw new InvalidTokenEndpointAuthMethodException($auth_type);

        $this->client_secret = $client_secret;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->client_secret;
    }
}