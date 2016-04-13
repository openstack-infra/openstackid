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
use jwk\exceptions\InvalidJWKAlgorithm;
use jwk\exceptions\JWKInvalidSpecException;
use jwk\IJWK;
use OAuth2\Exceptions\InvalidClientType;
use OAuth2\Exceptions\RecipientKeyNotFoundException;
use OAuth2\Models\IClient;

/**
 * Interface IKeyFinder
 * @package OAuth2\Heuristics
 */
interface IKeyFinder
{
    /**
     * @param  IClient $client
     * @param  ICryptoAlgorithm $alg
     * @param  string|null $kid_hint
     * @return IJWK
     * @throws InvalidClientType
     * @throws RecipientKeyNotFoundException
     * @throws InvalidJWKAlgorithm
     * @throws JWKInvalidSpecException
     */
    public function find(IClient $client, ICryptoAlgorithm $alg, $kid_hint = null);
}