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

namespace oauth2\heuristics;

use jwa\cryptographic_algorithms\ICryptoAlgorithm;
use jwa\cryptographic_algorithms\key_management\modes\DirectEncryption;
use jwk\exceptions\InvalidJWKAlgorithm;
use jwk\exceptions\JWKInvalidSpecException;
use jwk\IJWK;
use jwk\impl\OctetSequenceJWKFactory;
use jwk\impl\OctetSequenceJWKSpecification;
use jwk\JSONWebKeyPublicKeyUseValues;
use jwk\JSONWebKeyTypes;
use oauth2\exceptions\InvalidClientType;
use oauth2\exceptions\ServerKeyNotFoundException;
use oauth2\models\IClient;
use oauth2\repositories\IServerPrivateKeyRepository;

/**
 * Class ServerEncryptionKeyFinder
 * @package oauth2\heuristics
 */
final class ServerEncryptionKeyFinder implements IKeyFinder
{
    /**
     * @var IServerPrivateKeyRepository
     */
    private $server_private_key_repository;

    /**
     * @param IServerPrivateKeyRepository $server_private_key_repository
     */
    public function __construct(IServerPrivateKeyRepository $server_private_key_repository)
    {
        $this->server_private_key_repository = $server_private_key_repository;
    }

    /**
     * @param  IClient $client
     * @param  ICryptoAlgorithm $alg
     * @param  string|null $kid_hint
     * @return IJWK
     * @throws InvalidClientType
     * @throws ServerKeyNotFoundException
     * @throws InvalidJWKAlgorithm
     * @throws JWKInvalidSpecException
     */
    public function find(IClient $client, ICryptoAlgorithm $alg, $kid_hint = null)
    {
        if($alg instanceof DirectEncryption)
        {
            // use secret
            if($client->getClientType() !== IClient::ClientType_Confidential)
                throw new InvalidClientType;

            $jwk = OctetSequenceJWKFactory::build
            (
                new OctetSequenceJWKSpecification
                (
                    $client->getClientSecret(),
                    $alg->getName()
                )
            );

            $jwk->setId('shared_secret');

            return $jwk;
        }

        $key = null;

        if(!is_null($kid_hint))
        {
            $key = $this->server_private_key_repository->get($kid_hint);
            if(!$key->isActive()) $key = null;
            if($key->getAlg()->getName() !== $alg->getName()) $key = null;
        }

        if(is_null($key))
        {
            $key = $this->server_private_key_repository->getActiveByCriteria
            (
                JSONWebKeyTypes::RSA,
                JSONWebKeyPublicKeyUseValues::Encryption,
                $alg->getName()
            );
        }

        if(is_null($key))
            throw new ServerKeyNotFoundException
            (
                sprintf('enc key not found  - client id %s - requested alg %s', $client->getClientId(), $alg->getName())
            );

        $jwk = $key->toJWK();

        $key->markAsUsed();
        $key->save();

        return $jwk;
    }
}