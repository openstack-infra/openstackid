<?php

namespace services;

use Log;
use openid\exceptions\OpenIdInvalidRealmException;
use openid\exceptions\ReplayAttackException;
use openid\exceptions\InvalidAssociation;

use openid\helpers\OpenIdErrorMessages;
use openid\model\IAssociation;
use openid\services\IAssociationService;
use OpenIdAssociation;
use utils\exceptions\UnacquiredLockException;
use utils\services\ILockManagerService;
use utils\services\ICacheService;
/**
 * Class AssociationService
 * @package services
 */
class AssociationService implements IAssociationService
{

    private $lock_manager_service;
    private $cache_service;
    public function __construct(ILockManagerService $lock_manager_service, ICacheService $cache_service)
    {
        $this->lock_manager_service = $lock_manager_service;
        $this->cache_service        = $cache_service;
    }

    /**
     * gets a given association by handle, and if association exists and its type is private, then lock it
     * to prevent subsequent usage ( private association could be used once)
     * @param $handle
     * @param null $realm
     * @return null|IAssociation|OpenIdAssociation
     * @throws \openid\exceptions\ReplayAttackException
     * @throws \openid\exceptions\InvalidAssociation
     * @throws \openid\exceptions\OpenIdInvalidRealmException
     */
    public function getAssociation($handle, $realm = null)
    {

        $lock_name = 'lock.get.assoc.' . $handle;

        try {
            // check if association is on cache
            if (!$this->cache_service->exists($handle)) {
                // if not , check on db
                $assoc = OpenIdAssociation::where('identifier', '=', $handle)->first();
                if(is_null($assoc))
                    throw new InvalidAssociation(sprintf('openid association %s does not exists!',$handle));
                //check association lifetime ...
                $remaining_lifetime = $assoc->getRemainingLifetime();
                if ($remaining_lifetime < 0) {
                    $this->deleteAssociation($handle);
                    return null;
                }

                //repopulate cache
                $this->cache_service->storeHash($handle, array(
                    "type"         => $assoc->type,
                    "mac_function" => $assoc->mac_function,
                    "issued"       => $assoc->issued,
                    "lifetime"     => $assoc->lifetime,
                    "secret"       => \bin2hex($assoc->secret),
                    "realm"        => $assoc->realm),
                    $remaining_lifetime);
            }

            //get hash from cache
            $cache_values = $this->cache_service->getHash($handle, array(
                "type",
                "mac_function",
                "issued",
                "lifetime",
                "secret",
                "realm"));

            if ($cache_values['type'] == IAssociation::TypePrivate) {
                if (is_null($realm) || empty($realm) || $cache_values['realm'] != $realm) {
                    throw new OpenIdInvalidRealmException(sprintf(OpenIdErrorMessages::InvalidPrivateAssociationMessage, $handle, $realm));
                }
                // only one time we could use this handle
                $this->lock_manager_service->acquireLock($lock_name);
            }

            $assoc = new OpenIdAssociation();
            $assoc->type         = $cache_values['type'];
            $assoc->mac_function = $cache_values['mac_function'];
            $assoc->issued       = $cache_values['issued'];
            $assoc->lifetime     = $cache_values['lifetime'];
            $assoc->secret       = \hex2bin($cache_values['secret']);
            $realm               = $cache_values['realm'];
            if (!empty($realm))
                $assoc->realm = $realm;
            return $assoc;

        } catch (UnacquiredLockException $ex1) {
            throw new ReplayAttackException(sprintf(OpenIdErrorMessages::ReplayAttackPrivateAssociationAlreadyUsed, $handle));
        }
    }

    /**
     * @param $handle
     * @return bool
     */
    public function deleteAssociation($handle)
    {
        $this->cache_service->delete($handle);
        $assoc = OpenIdAssociation::where('identifier', '=', $handle)->first();
        if (!is_null($assoc)) {
            $assoc->delete();
            return true;
        }
        return false;
    }

    /**
     * @param $handle
     * @param $secret
     * @param $mac_function
     * @param $lifetime
     * @param $issued
     * @param $type
     * @param null $realm
     * @return IAssociation
     * @throws \openid\exceptions\ReplayAttackException
     */
    public function addAssociation($handle, $secret, $mac_function, $lifetime, $issued, $type, $realm = null)
    {
        $assoc = new OpenIdAssociation();
        try {
            $lock_name = 'lock.add.assoc.' . $handle;
            $this->lock_manager_service->acquireLock($lock_name);

            $assoc->identifier   = $handle;
            $assoc->secret       = $secret;
            $assoc->type         = $type;
            $assoc->mac_function = $mac_function;
            $assoc->lifetime = $lifetime;
            $assoc->issued = $issued;
            if (!is_null($realm))
                $assoc->realm = $realm;

            if ($type == IAssociation::TypeSession) {
                $assoc->Save();
            }

            if (is_null($realm))
                $realm = '';

            $this->cache_service->storeHash($handle, array(
                "type" => $type,
                "mac_function" => $mac_function,
                "issued" => $issued,
                "lifetime" => $lifetime,
                "secret" => \bin2hex($secret),
                "realm" => $realm),$lifetime);


        } catch (UnacquiredLockException $ex1) {
            throw new ReplayAttackException(sprintf(OpenIdErrorMessages::ReplayAttackPrivateAssociationAlreadyUsed, $handle));
        }
        return $assoc;
    }

    /**
     * For verifying signatures an OP MUST only use private associations and MUST NOT
     * use associations that have shared keys. If the verification request contains a handle
     * for a shared association, it means the Relying Party no longer knows the shared secret,
     * or an entity other than the RP (e.g. an attacker) has established this association with the OP.
     * @param $handle
     * @return mixed
     */
    public function getAssociationType($handle)
    {
        $assoc = OpenIdAssociation::where('identifier', '=', $handle)->first();
        if (!is_null($assoc)) {
            return $assoc->type;
        }
        return false;
    }
}