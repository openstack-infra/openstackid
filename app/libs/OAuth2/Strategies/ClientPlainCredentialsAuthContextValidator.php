<?php namespace OAuth2\Strategies;

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

use OAuth2\Exceptions\InvalidClientAuthenticationContextException;
use OAuth2\Exceptions\InvalidClientCredentials;
use OAuth2\Models\ClientAuthenticationContext;
use OAuth2\Models\ClientCredentialsAuthenticationContext;
use OAuth2\Models\IClient;

/**
 * Class ClientPlainCredentialsAuthContextValidator
 * @package OAuth2\Strategies
 */
final class ClientPlainCredentialsAuthContextValidator implements IClientAuthContextValidator
{

    /**
     * @param ClientAuthenticationContext $context
     * @return bool
     * @throws InvalidClientAuthenticationContextException
     * @throws InvalidClientCredentials
     */
    public function validate(ClientAuthenticationContext $context)
    {
        if(!($context instanceof ClientCredentialsAuthenticationContext))
            throw new InvalidClientAuthenticationContextException;

        if(is_null($context->getClient()))
            throw new InvalidClientAuthenticationContextException('client not set!');

        if($context->getClient()->getTokenEndpointAuthInfo()->getAuthenticationMethod() !== $context->getAuthType())
            throw new InvalidClientCredentials(sprintf('invalid token endpoint auth method %s', $context->getAuthType()));

        if($context->getClient()->getClientType() !== IClient::ClientType_Confidential)
            throw new InvalidClientCredentials(sprintf('invalid client type %s', $context->getClient()->getClientType()));


        return $context->getClient()->getClientId()     === $context->getId() &&
               $context->getClient()->getClientSecret() === $context->getSecret();
    }
}