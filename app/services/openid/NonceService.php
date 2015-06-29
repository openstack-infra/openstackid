<?php

namespace services\openid;

use Exception;
use Log;
use openid\exceptions\ReplayAttackException;
use openid\helpers\OpenIdErrorMessages;
use openid\model\OpenIdNonce;
use openid\services\INonceService;
use utils\exceptions\UnacquiredLockException;
use utils\services\IdentifierGenerator;
use utils\services\ILockManagerService;
use utils\services\ICacheService;
use utils\services\IServerConfigurationService;
use Zend\Math\Rand;

/**
 * Class NonceService
 * @package services\openid
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
     * @return bool
     */
    public function lockNonce(OpenIdNonce $nonce)
    {
        $raw_nonce = $nonce->getRawFormat();
        $lock_lifetime = $this->configuration_service->getConfigValue("Nonce.Lifetime");
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
     * @return string
     */
    private function makeNonceSalt(){
        return Rand::getString(self::NonceSaltLength, self::NoncePopulation, true);
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
            $lifetime  = $this->configuration_service->getConfigValue("Nonce.Lifetime");
            $this->cache_service->setSingleValue($raw_nonce . $signature, $realm, $lifetime );
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }
}