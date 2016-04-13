<?php namespace Services\OpenId;

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

use Exception;
use Illuminate\Support\Facades\Log;
use OpenId\Exceptions\ReplayAttackException;
use OpenId\Helpers\OpenIdErrorMessages;
use OpenId\Models\OpenIdNonce;
use OpenId\Services\INonceService;
use Utils\Exceptions\UnacquiredLockException;
use Utils\Services\ICacheService;
use Utils\Services\IdentifierGenerator;
use Utils\Services\ILockManagerService;
use Utils\Services\IServerConfigurationService;

/**
 * Class NonceService
 * @package Services\OpenId
 */
final class NonceService implements INonceService
{

    /**
     * @var ICacheService
     */
    private $cache_service;
    /**
     * @var ILockManagerService
     */
    private $lock_manager_service;
    /**
     * @var IServerConfigurationService
     */
    private $configuration_service;
    /**
     * @var IdentifierGenerator
     */
    private $nonce_generator;

    /**
     * @param ILockManagerService $lock_manager_service
     * @param ICacheService $cache_service
     * @param IServerConfigurationService $configuration_service
     * @param IdentifierGenerator $nonce_generator
     */
    public function __construct(ILockManagerService $lock_manager_service,
                                ICacheService $cache_service,
                                IServerConfigurationService $configuration_service,
                                IdentifierGenerator $nonce_generator)
    {
        $this->lock_manager_service  = $lock_manager_service;
        $this->cache_service         = $cache_service;
        $this->configuration_service = $configuration_service;
        $this->nonce_generator       = $nonce_generator;
    }

    /**
     * @param OpenIdNonce $nonce
     * @throws ReplayAttackException
     * @return $this
     */
    public function lockNonce(OpenIdNonce $nonce)
    {
        $raw_nonce     = $nonce->getRawFormat();
        $lock_lifetime = $this->configuration_service->getConfigValue("Nonce.Lifetime");
        try {
            $this->lock_manager_service->acquireLock('lock.nonce.' . $raw_nonce, $lock_lifetime);
        } catch (UnacquiredLockException $ex) {
            throw new ReplayAttackException(sprintf(OpenIdErrorMessages::ReplayAttackNonceAlreadyUsed, $nonce->getRawFormat()));
        }
        return $this;
    }

    /**
     * @param OpenIdNonce $nonce
     * @return $this
     */
    public function unlockNonce(OpenIdNonce $nonce)
    {
        $raw_nonce = $nonce->getRawFormat();
        $this->lock_manager_service->releaseLock('lock.nonce.' . $raw_nonce);
        return $this;
    }


    /**
     * Value: A string 255 characters or less in length, that MUST be unique to this particular successful
     * authentication response. The nonce MUST start with the current time on the server, and MAY contain additional
     * ASCII characters in the range 33-126 inclusive (printable non-whitespace characters), as necessary to make each
     * response unique. The date and time MUST be formatted as specified in section 5.6 of [RFC3339], with the following
     * restrictions:
     * All times must be in the UTC timezone, indicated with a "Z".
     * No fractional seconds are allowed
     * For example: 2005-05-15T17:11:51ZUNIQUE
     * @return OpenIdNonce
     */
    public function generateNonce()
    {
        return $this->nonce_generator->generate(new OpenIdNonce(255));
    }

    /**
     * @param OpenIdNonce $nonce
     * @param string $signature
     * @param string $realm
     * @return $this
     * @throws ReplayAttackException
     */
    public function markNonceAsInvalid(OpenIdNonce $nonce, $signature, $realm)
    {
        $raw_nonce = $nonce->getRawFormat();
        $key = $raw_nonce . $signature;

        try {
            if (!$this->cache_service->exists($key))
                throw new ReplayAttackException(sprintf(OpenIdErrorMessages::ReplayAttackNonceAlreadyUsed, $nonce->getRawFormat()));
            $old_realm = $this->cache_service->getSingleValue($key);
            if ($realm != $old_realm) {
                throw new ReplayAttackException(sprintf(OpenIdErrorMessages::ReplayAttackNonceAlreadyEmittedForAnotherRealm, $realm));
            }
            $this->cache_service->delete($key);
        } catch (ReplayAttackException $ex) {
            $this->cache_service->delete($key);
            throw $ex;
        }
        return $this;
    }

    /**
     * @param OpenIdNonce $nonce
     * @param string $signature
     * @param string $realm
     * @return $this;
     */
    public function associateNonce(OpenIdNonce $nonce, $signature, $realm)
    {
        try {
            $raw_nonce = $nonce->getRawFormat();
            $lifetime  = $this->configuration_service->getConfigValue("Nonce.Lifetime");
            $this->cache_service->setSingleValue($raw_nonce . $signature, $realm, $lifetime );
        } catch (Exception $ex) {
            Log::error($ex);
        }
        return $this;
    }
}