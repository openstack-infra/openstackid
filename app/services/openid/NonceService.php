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


class NonceService implements INonceService
{

    private $redis;

    public function __construct(ILockManagerService $lock_manager_service)
    {
        $this->redis = \RedisLV4::connection();
        $this->lock_manager_service = $lock_manager_service;
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
            if ($this->redis->exists($key) == 0)
                throw new ReplayAttackException(sprintf(OpenIdErrorMessages::ReplayAttackNonceAlreadyUsed, $nonce->getRawFormat()));
            $old_realm = $this->redis->get($key);
            if ($realm != $old_realm) {
                throw new ReplayAttackException(sprintf(OpenIdErrorMessages::ReplayAttackNonceAlreadyEmittedForAnotherRealm, $realm));
            }
            $this->redis->del($key);
        } catch (ReplayAttackException $ex) {
            $this->redis->del($key);
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
            $this->redis->setex($raw_nonce . $signature, $lifetime, $realm);
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }
}