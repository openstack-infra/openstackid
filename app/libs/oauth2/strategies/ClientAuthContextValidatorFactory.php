<?php
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

namespace oauth2\strategies;

use oauth2\exceptions\InvalidTokenEndpointAuthMethodException;
use oauth2\models\ClientAuthenticationContext;
use oauth2\OAuth2Protocol;
use oauth2\services\IClientJWKSetReader;

/**
 * Class ClientAuthContextValidatorFactory
 * @package oauth2\strategies
 */
final class ClientAuthContextValidatorFactory
{
    /**
     * @var string
     */
    static private $token_endpoint_url;

    /**
     * @var IClientJWKSetReader
     */
    static private $jwks_reader;

    /**
     * @param string $token_endpoint_url
     */
    static public function setTokenEndpointUrl($token_endpoint_url)
    {
        self::$token_endpoint_url = $token_endpoint_url;
    }

    /**
     * @param IClientJWKSetReader $jwks_reader
     */
    static public function setJWKSetReader(IClientJWKSetReader $jwks_reader)
    {
        self::$jwks_reader = $jwks_reader;
    }

    /**
     * @param ClientAuthenticationContext $context
     * @return IClientAuthContextValidator
     */
    static public function build(ClientAuthenticationContext $context)
    {
        switch($context->getAuthType())
        {
            case OAuth2Protocol::TokenEndpoint_AuthMethod_ClientSecretBasic:
            case OAuth2Protocol::TokenEndpoint_AuthMethod_ClientSecretPost:
            {
                return new ClientPlainCredentialsAuthContextValidator;
            }
            break;
            case OAuth2Protocol::TokenEndpoint_AuthMethod_ClientSecretJwt:
            {
                $validator =  new ClientSharedSecretAssertionAuthContextValidator;
                $validator->setTokenEndpointUrl(self::$token_endpoint_url);
                return $validator;
            }
            break;
            case OAuth2Protocol::TokenEndpoint_AuthMethod_PrivateKeyJwt:
            {
                $validator = new ClientPrivateKeyAssertionAuthContextValidator;
                $validator->setTokenEndpointUrl(self::$token_endpoint_url);
                $validator->setJWKSetReader(self::$jwks_reader );
                return $validator;
            }
            break;
        }
        throw new InvalidTokenEndpointAuthMethodException($context->getAuthType());
    }
}