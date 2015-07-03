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
use oauth2\exceptions\InvalidClientAuthenticationContextException;
use oauth2\exceptions\RecipientKeyNotFoundException;
use oauth2\models\ClientAssertionAuthenticationContext;
use oauth2\models\ClientAuthenticationContext;
use utils\json_types\JsonValue;

/**
 * Class ClientAssertionAuthContextValidator
 * @package oauth2\strategies
 */
abstract class ClientAssertionAuthContextValidator implements IClientAuthContextValidator
{

    /**
     * @var string
     */
    private $token_endpoint_url;

    /**
     * @param string $token_endpoint_url
     * @return $this
     */
    public function setTokenEndpointUrl($token_endpoint_url)
    {
        $this->token_endpoint_url = $token_endpoint_url;
        return $this;
    }

    /**
     * @param ClientAuthenticationContext $context
     * @param  JsonValue $kid
     * @throws InvalidClientAuthenticationContextException
     * @return IJWK
     */
    abstract protected function getKey(ClientAuthenticationContext $context, JsonValue $kid = null);

    /**
     * @param ClientAuthenticationContext $context
     * @return bool
     */
    public function validate(ClientAuthenticationContext $context)
    {
        if(!($context instanceof ClientAssertionAuthenticationContext))
            throw new InvalidClientAuthenticationContextException;

        if(empty($this->token_endpoint_url))
            throw new InvalidClientAuthenticationContextException('token_endpoint_url not set!');

        if( $context->getClient()->getTokenEndpointAuthInfo()->getAuthenticationMethod() !== $context->getAuthType())
            return false;

        $jws          = $context->getAssertion();

        $client       = $context->getClient();

        $original_alg = $client->getTokenEndpointAuthInfo()->getSigningAlgorithm()->getName();

        if($original_alg !== $jws->getJOSEHeader()->getAlgorithm()->getString())
            return false;

        $key = $this->getKey
        (
            $context,
            $jws->getJOSEHeader()->getKeyID()
        );

        if(is_null($key))
            return false;

        $verified = $jws->setKey($key)->verify($original_alg);

        if(!$verified) return false;

        $iss = $jws->getClaimSet()->getIssuer()->getString();
        $aud = $jws->getClaimSet()->getAudience()->getString();
        $sub = $jws->getClaimSet()->getSubject()->getString();
        $jti = $jws->getClaimSet()->getJWTID();

        //todo: prevent reuse of the token
        if(empty($jti)) return false;

        if($iss !== $sub)
            throw new InvalidClientAuthenticationContextException('iss not match with sub!');

        if($aud !== $this->token_endpoint_url)
            return false;

        if($jws->isExpired(180)) return false;

        return true;

    }
}