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

namespace oauth2\models;

use jwa\cryptographic_algorithms\DigitalSignatures_MACs_Registry;
use jwa\cryptographic_algorithms\macs\MAC_Algorithm;
use jws\IJWS;
use jwt\IBasicJWT;
use oauth2\exceptions\InvalidClientAssertionAlgorithmException;
use oauth2\exceptions\InvalidClientAssertionException;
use oauth2\exceptions\InvalidClientAssertionTypeException;
use oauth2\exceptions\InvalidTokenEndpointAuthMethodException;
use oauth2\OAuth2Protocol;
use utils\factories\BasicJWTFactory;
use utils\exceptions\InvalidCompactSerializationException;

/**
 * Class ClientAssertionAuthenticationContext
 * @package oauth2\models
 * https://tools.ietf.org/html/rfc7523#section-2.2
 */
final class ClientAssertionAuthenticationContext extends ClientAuthenticationContext
{
    const RegisteredAssertionType = 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer';
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $jwt_assertion;

    /**
     * @var IJWS
     */
    private $jws;

    /**
     * @param string $type
     * @param string $jwt_assertion
     * @throws InvalidClientAssertionAlgorithmException
     * @throws InvalidClientAssertionTypeException
     * @throws InvalidTokenEndpointAuthMethodException
     * @throws InvalidCompactSerializationException
     * @throws InvalidClientAssertionException
     */
    public function __construct($type, $jwt_assertion)
    {

        if($type !== self::RegisteredAssertionType)
            throw new InvalidClientAssertionTypeException($type);

        if(empty($jwt_assertion))
            throw new InvalidClientAssertionException;

        $this->type          = $type;
        $this->jwt_assertion = $jwt_assertion;

        $this->jws = BasicJWTFactory::build($jwt_assertion);

        if(!($this->jws instanceof IJWS))
            throw new InvalidClientAssertionException;

        $alg = $this->jws->getJOSEHeader()->getAlgorithm()->getValue();

        if( !(DigitalSignatures_MACs_Registry::getInstance()->isSupported($alg) &&
            in_array($alg, OAuth2Protocol::$supported_signing_algorithms)))
                throw new InvalidClientAssertionAlgorithmException($alg);

        $alg = DigitalSignatures_MACs_Registry::getInstance()->get($alg);

        $auth_type = $alg instanceof MAC_Algorithm ?
                            OAuth2Protocol::TokenEndpoint_AuthMethod_ClientSecretJwt :
                            OAuth2Protocol::TokenEndpoint_AuthMethod_PrivateKeyJwt;

        parent::__construct($this->jws->getClaimSet()->getIssuer()->getString(), $auth_type);
    }

    /**
     * @return IJWS
     */
    public function getAssertion()
    {
        return $this->jws;
    }
}