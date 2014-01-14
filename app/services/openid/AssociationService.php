<?php

namespace services;

use Log;
use openid\exceptions\OpenIdInvalidRealmException;
use openid\exceptions\ReplayAttackException;
use openid\helpers\OpenIdErrorMessages;
use openid\model\IAssociation;
use openid\services\IAssociationService;
use OpenIdAssociation;
use utils\exceptions\UnacquiredLockException;
use utils\services\ILockManagerService;

/**
 * Class AssociationService
 * @package services
 */
class AssociationService implements IAssociationService
{

    private $redis;
    private $lock_manager_service;

    public function __construct(ILockManagerService $lock_manager_service)
    {
        $this->redis = \RedisLV4::connection();
        $this->lock_manager_service = $lock_manager_service;
    }

    /**
     * gets a given association by handle, and if association exists and its type is private, then lock it
     * to prevent subsequent usage ( private association could be used once)
     * @param $handle
     * @param null $realm
     * @return null|IAssociation
     * @throws \openid\exceptions\ReplayAttackException
     * @throws \openid\exceptions\OpenIdInvalidRealmException
     */
    public function getAssociation($handle, $realm = null)
    {

        $lock_name = 'lock.get.assoc.' . $handle;

        try {
            // check if association is on redis cache
            if (!$this->redis->exists($handle)) {
                // if not , check on db
                $assoc = OpenIdAssociation::where('identifier', '=', $handle)->first();
                if(is_null($assoc))
                    throw new ReplayAttackException(sprintf('openid association %s does not exists!',$handle));
                //check association lifetime ...
                $remaining_lifetime = $assoc->getRemainingLifetime();
                if ($remaining_lifetime < 0) {
                    $this->deleteAssociation($handle);
                    return null;
                }

                //repopulate redis
                $this->redis->hmset($handle, array(
                    "type"         => $assoc->type,
                    "mac_function" => $assoc->mac_function,
                    "issued"       => $assoc->issued,
                    "lifetime"     => $assoc->lifetime,
                    "secret"       => \bin2hex($assoc->secret),
                    "realm"        => $assoc->realm));
                $this->redis->expire($handle, $remaining_lifetime);
            }

            //get hash from redis
            $values = $this->redis->hmget($handle, array(
                "type",
                "mac_function",
                "issued",
                "lifetime",
                "secret",
                "realm"));

            if ($values[0] == IAssociation::TypePrivate) {
                if (is_null($realm) || empty($realm) || $values[5] != $realm) {
                    throw new OpenIdInvalidRealmException(sprintf(OpenIdErrorMessages::InvalidPrivateAssociationMessage, $handle, $realm));
                }
                // only one time we could use this handle
                $this->lock_manager_service->acquireLock($lock_name);
            }

            $assoc = new OpenIdAssociation();
            $assoc->type         = $values[0];
            $assoc->mac_function = $values[1];
            $assoc->issued       = $values[2];
            $assoc->lifetime     = $values[3];
            $assoc->secret       = \hex2bin($values[4]);
            $realm               = $values[5];
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
        $this->redis->del($handle);
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

            $assoc->identifier = $handle;
            $assoc->secret = $secret;
            $assoc->type = $type;
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

            $this->redis->hmset($handle, array(
                "type" => $type,
                "mac_function" => $mac_function,
                "issued" => $issued,
                "lifetime" => $lifetime,
                "secret" => \bin2hex($secret),
                "realm" => $realm));

            $this->redis->expire($handle, $lifetime);

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