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
use jwk\JSONWebKeyPublicKeyUseValues;
use oauth2\exceptions\InvalidClientAuthenticationContextException;
use oauth2\models\ClientAssertionAuthenticationContext;
use oauth2\models\ClientAuthenticationContext;
use oauth2\services\IClientJWKSetReader;
use utils\json_types\JsonValue;

/**
 * Class ClientPrivateKeyAssertionAuthContextValidator
 * @package oauth2\strategies
 */
final class ClientPrivateKeyAssertionAuthContextValidator extends ClientAssertionAuthContextValidator
{

    /**
     * @var IClientJWKSetReader
     */
    private $jwks_reader;

    /**
     * @param IClientJWKSetReader $jwks_reader
     * @return $this
     */
    public function setJWKSetReader(IClientJWKSetReader $jwks_reader)
    {
        $this->jwks_reader = $jwks_reader;

        return $this;
    }

    /**
     * @param ClientAuthenticationContext $context
     * @param JsonValue $kid
     * @throws InvalidClientAuthenticationContextException
     * @return IJWK
     */
    protected function getKey(ClientAuthenticationContext $context, JsonValue $kid = null)
    {

        if(!($context instanceof ClientAssertionAuthenticationContext))
            throw new InvalidClientAuthenticationContextException;

        $client = $context->getClient();

        $jws    = $context->getAssertion();


        if(!is_null($kid))
        {
            $key = $client->getPublicKeyByIdentifier($kid->getValue());
            if($key->isActive() && !$key->isExpired()) return $key->toJWK();
        }
        $alg =   $jws->getJOSEHeader()->getAlgorithm()->getString();

        $key = $client->getCurrentPublicKeyByUse
        (
            JSONWebKeyPublicKeyUseValues::Signature,
            $alg
        );

        if(!is_null($key)) return $key->toJWK();

        // no public keys set, try with jwks_url ...
        if (is_null($this->jwks_reader))
        {
            throw new InvalidClientAuthenticationContextException('jwks_reader not set!');
        }

        $jwk_set = $this->jwks_reader->read($client);

        if(!is_null($kid))
        {
            $key = $jwk_set->getKeyById($kid->getValue());
            if(!is_null($key)) return $key;
        }

        foreach ($jwk_set->getKeys() as $key)
        {
            if
            (
                $key->getKeyUse() === JSONWebKeyPublicKeyUseValues::Signature &&
                $key->getAlgorithm()->getString() === $alg
            )
            {
                return $key;
            }
        }

        return null;
    }
}