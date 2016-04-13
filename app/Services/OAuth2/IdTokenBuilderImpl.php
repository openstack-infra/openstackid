<?php namespace Services\OAuth2;
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

use jwe\IJWE;
use jwe\impl\JWEFactory;
use jwe\impl\specs\JWE_ParamsSpecification;
use jwk\exceptions\InvalidJWKAlgorithm;
use jwk\exceptions\InvalidJWKType;
use jwk\IJWK;
use jws\IJWS;
use jws\impl\specs\JWS_ParamsSpecification;
use jws\JWSFactory;
use jwt\IBasicJWT;
use jwt\IJWT;
use jwt\impl\JWTClaimSet;
use jwt\impl\UnsecuredJWT;
use OAuth2\Builders\IdTokenBuilder;
use OAuth2\Exceptions\InvalidClientType;
use OAuth2\Exceptions\RecipientKeyNotFoundException;
use OAuth2\Exceptions\ServerKeyNotFoundException;
use OAuth2\Heuristics\ClientEncryptionKeyFinder;
use OAuth2\Heuristics\ServerSigningKeyFinder;
use OAuth2\Models\IClient;
use OAuth2\Models\JWTResponseInfo;
use OAuth2\Repositories\IServerPrivateKeyRepository;
use OAuth2\Services\IClientJWKSetReader;
use utils\json_types\StringOrURI;

/**
 * Class IdTokenBuilderImpl
 * @package Services\OAuth2
 */
final class IdTokenBuilderImpl implements IdTokenBuilder
{

    /**
     * @var IServerPrivateKeyRepository
     */
    private $server_private_key_repository;

    /**
     * @var IClientJWKSetReader
     */
    private $jwk_set_reader_service;

    /**
     * @param IServerPrivateKeyRepository $server_private_key_repository
     * @param IClientJWKSetReader $jwk_set_reader_service
     */
    public function __construct
    (
        IServerPrivateKeyRepository $server_private_key_repository,
        IClientJWKSetReader $jwk_set_reader_service
    )
    {
        $this->server_private_key_repository = $server_private_key_repository;
        $this->jwk_set_reader_service        = $jwk_set_reader_service;
    }

    /**
     * @param JWTClaimSet $claim_set
     * @param JWTResponseInfo $info
     * @param IClient $client
     * @return IJWT
     * @throws RecipientKeyNotFoundException
     * @throws InvalidClientType
     * @throws ServerKeyNotFoundException
     */
    public function buildJWT(JWTClaimSet $claim_set, JWTResponseInfo $info, IClient $client)
    {
        $sig_alg                = $info->getSigningAlgorithm();
        $enc_alg                = $info->getEncryptionKeyAlgorithm();
        $enc                    = $info->getEncryptionContentAlgorithm();

        $jwt = UnsecuredJWT::fromClaimSet($claim_set);

        if(!is_null($sig_alg))
        {
            // must sign
            // get server private key to sign

            $heuristic = new ServerSigningKeyFinder($this->server_private_key_repository);

            $jwt = self::buildJWS
            (
                $heuristic->find
                (
                    $client,
                    $sig_alg
                ),
                $sig_alg->getName(),
                $claim_set
            );

        }

        if(!is_null($enc_alg) && !is_null($enc))
        {
            //encrypt , get client public key

            $alg     = new  StringOrURI($enc_alg->getName());
            $enc     = new  StringOrURI($enc->getName());

            //encrypt jwt as payload

            $heuristic = new ClientEncryptionKeyFinder($this->jwk_set_reader_service);

            $jwt = self::buildJWE
            (
                $heuristic->find
                (
                    $client,
                    $enc_alg
                ),
                $alg,
                $enc,
                $jwt
            );
        }

        return $jwt;
    }

    /**
     * @param IJWK $recipient_key
     * @param StringOrURI $alg
     * @param StringOrURI $enc
     * @param IBasicJWT $jwt
     * @return IJWE
     * @throws RecipientKeyNotFoundException
     * @throws InvalidJWKAlgorithm
     * @throws InvalidJWKType
     */
    static private function buildJWE(IJWK $recipient_key, StringOrURI $alg, StringOrURI $enc, IBasicJWT $jwt)
    {

        if(is_null($recipient_key))
            throw new RecipientKeyNotFoundException;

        $jwe = JWEFactory::build
        (
            new JWE_ParamsSpecification
            (
                $recipient_key,
                $alg,
                $enc,
                $payload = $jwt->toCompactSerialization()
            )
        );
        return $jwe;
    }

    /**
     * @param IJWK $jwk
     * @param $alg
     * @param JWTClaimSet $claim_set
     * @return IJWS
     * @throws InvalidJWKAlgorithm
     * @throws InvalidJWKType
     */
    static private function buildJWS(IJWK $jwk, $alg, JWTClaimSet $claim_set)
    {
        return JWSFactory::build
        (
            new JWS_ParamsSpecification
            (
                $jwk,
                new StringOrURI
                (
                    $alg
                ),
                $claim_set
            )
        );
    }
}