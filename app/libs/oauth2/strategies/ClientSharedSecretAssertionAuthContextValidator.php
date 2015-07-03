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

use jwk\IJWK;
use jwk\impl\OctetSequenceJWKFactory;
use jwk\impl\OctetSequenceJWKSpecification;
use oauth2\exceptions\InvalidClientAuthenticationContextException;
use oauth2\models\ClientAssertionAuthenticationContext;
use oauth2\models\ClientAuthenticationContext;
use utils\json_types\JsonValue;

/**
 * Class ClientSharedSecretAssertionAuthContextValidator
 * @package oauth2\strategies
 */
final class ClientSharedSecretAssertionAuthContextValidator extends ClientAssertionAuthContextValidator
{

    /**
     * client_secret_jwt
     * Clients that have received a client_secret value from the Authorization Server create a JWT using an HMAC SHA
     * algorithm, such as HMAC SHA-256. The HMAC (Hash-based Message Authentication Code) is calculated using the octets
     * of the UTF-8 representation of the client_secret as the shared key.
     * @param JsonValue $kid
     * @param ClientAuthenticationContext $context
     * @return IJWK
     */
    protected function getKey(ClientAuthenticationContext $context, JsonValue $kid = null)
    {
        if(!($context instanceof ClientAssertionAuthenticationContext))
            throw new InvalidClientAuthenticationContextException;

        $client = $context->getClient();

        $jws    = $context->getAssertion();

        $key    = OctetSequenceJWKFactory::build
        (
            new OctetSequenceJWKSpecification
            (
                $client->getClientSecret(),
                $jws->getJOSEHeader()->getAlgorithm()->getString()
            )
        );

        $key->setId('client_shared_secret');

        return $key;
    }
}