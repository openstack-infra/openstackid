<?php

namespace services;

use Exception;
use Log;
use openid\exceptions\ReplayAttackException;
use openid\helpers\OpenIdErrorMessages;
use openid\model\OpenIdNonce;
use openid\services\INonceService;
use utils\exceptions\UnacquiredLockException;
use utils\services\ILockManagerService;
use utils\services\ICacheService;

class NonceService implements INonceService
{


    private $cache_service;
    private $lock_manager_service;

    public function __construct(ILockManagerService $lock_manager_service,ICacheService $cache_service)
    {
        $this->lock_manager_service = $lock_manager_service;
        $this->cache_service        = $cache_service;
    }

    /**
     * @param OpenIdNonce $nonce
     * @throws ReplayAttackException
     * @return bool
     */
    public function lockNonce(OpenIdNonce $nonce)
    {
        $raw_nonce = $nonce->getRawFormat();
        $lock_lifetime = \ServerConfigurationService::getConfigValue("Nonce.Lifetime");
        try {
            $this->lock_manager_service->acquireLock('lock.nonce.' . $raw_nonce, $lock_lifetime);
        } catch (UnacquiredLockException $ex) {
            throw new ReplayAttackException(sprintf(OpenIdErrorMessages::ReplayAttackNonceAlreadyUsed, $nonce->getRawFormat()));
        }
    }

    public function unlockNonce(OpenIdNonce $nonce)
    {
        $raw_nonce = $nonce->getRawFormat();
        $this->lock_manager_service->releaseLock('lock.nonce.' . $raw_nonce);
    }

    /**
     * @return OpenIdNonce
     */
    public function generateNonce()
    {
        $raw_nonce = gmdate('Y-m-d\TH:i:s\Z') . uniqid();
        return new OpenIdNonce($raw_nonce);
    }

    /**
     * @param OpenIdNonce $nonce
     * @param string $signature
     * @param string $realm
     * @return mixed|void
     * @throws \openid\exceptions\ReplayAttackException
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
    }

    /**
     * @param OpenIdNonce $nonce
     * @param string $signature
     * @param string $realm
     */
    public function associateNonce(OpenIdNonce $nonce, $signature, $realm)
    {
        try {
            $raw_nonce = $nonce->getRawFormat();
            $lifetime = \ServerConfigurationService::getConfigValue("Nonce.Lifetime");
            $this->cache_service->setSingleValue($raw_nonce . $signature, $realm, $lifetime );
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }
}