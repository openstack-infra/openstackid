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


use oauth2\exceptions\InvalidClientAuthenticationContextException;
use oauth2\models\ClientAuthenticationContext;
use oauth2\models\ClientCredentialsAuthenticationContext;
use oauth2\models\IClient;
use oauth2\OAuth2Protocol;

/**
 * Class ClientPlainCredentialsAuthContextValidator
 * @package oauth2\strategies
 */
final class ClientPlainCredentialsAuthContextValidator implements IClientAuthContextValidator
{

    /**
     * @param ClientAuthenticationContext $context
     * @return bool
     */
    public function validate(ClientAuthenticationContext $context)
    {
        if(!($context instanceof ClientCredentialsAuthenticationContext))
            throw new InvalidClientAuthenticationContextException;

        if(is_null($context->getClient()))
            throw new InvalidClientAuthenticationContextException;



        return
               $context->getClient()->getTokenEndpointAuthInfo() === $context->getAuthType() &&
               $context->getClient()->getClientType()   === IClient::ClientType_Confidential &&
               $context->getClient()->getClientId()     === $context->getId() &&
               $context->getClient()->getClientSecret() === $context->getSecret();
    }
}