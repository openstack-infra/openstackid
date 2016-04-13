<?php namespace OAuth2\Heuristics;

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

use jwa\cryptographic_algorithms\ICryptoAlgorithm;
use jwa\cryptographic_algorithms\key_management\modes\DirectEncryption;
use jwk\IJWK;
use jwk\impl\OctetSequenceJWKFactory;
use jwk\impl\OctetSequenceJWKSpecification;
use jwk\JSONWebKeyPublicKeyUseValues;
use OAuth2\Exceptions\InvalidClientType;
use OAuth2\Exceptions\RecipientKeyNotFoundException;
use OAuth2\Models\IClient;
use OAuth2\Services\IClientJWKSetReader;

/**
 * Class ClientEncryptionKeyFinder
 * @package OAuth2\Heuristics
 */
final class ClientEncryptionKeyFinder implements IKeyFinder
{
    /**
     * @var IClientJWKSetReader
     */
    private $jwk_set_reader_service;

    /**
     * @param IClientJWKSetReader $jwk_set_reader_service
     */
    public function __construct(IClientJWKSetReader $jwk_set_reader_service)
    {
        $this->jwk_set_reader_service = $jwk_set_reader_service;
    }

    /**
     * @param IClient $client
     * @param ICryptoAlgorithm $alg
     * @param null $kid_hint
     * @return IJWK
     * @throws InvalidClientType
     * @throws RecipientKeyNotFoundException
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


        $recipient_key = null;

        if(!is_null($kid_hint))
        {
            $recipient_key = $client->getPublicKeyByIdentifier($kid_hint);
            if(!$recipient_key->isActive()) $recipient_key = null;
            if($recipient_key->getAlg()->getName() !== $alg->getName()) $recipient_key = null;
        }

        if(is_null($recipient_key))
        {
            $recipient_key = $client->getCurrentPublicKeyByUse
            (
                JSONWebKeyPublicKeyUseValues::Encryption,
                $alg->getName()
            );
        }

        if(!is_null($recipient_key))
        {
            $recipient_key->markAsUsed();
            $recipient_key->save();
            $recipient_key = $recipient_key->toJWK();
        }
        else
        {
            // check on jwk uri
            $jwk_set = $this->jwk_set_reader_service->read($client);

            if(is_null($jwk_set))
                throw new RecipientKeyNotFoundException;

            foreach($jwk_set->getKeys() as $jwk)
            {
                if
                (
                    $jwk->getKeyUse() ===  JSONWebKeyPublicKeyUseValues::Encryption &&
                    $jwk->getAlgorithm()->getString() === $alg->getName()
                )
                {

                    $recipient_key = $jwk;
                    break;
                }
            }
        }

        if(is_null($recipient_key))
            throw new RecipientKeyNotFoundException;

        return $recipient_key;
    }
}