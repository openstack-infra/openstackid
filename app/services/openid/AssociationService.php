<?php

namespace services;

use DateInterval;
use DateTime;
use openid\exceptions\OpenIdInvalidRealmException;
use openid\exceptions\ReplayAttackException;
use openid\helpers\OpenIdErrorMessages;
use openid\model\IAssociation;
use openid\services\IAssociationService;
use OpenIdAssociation;
use Exception;
use Log;

class AssociationService implements IAssociationService
{

    private $redis;

    public function __construct()
    {
        $this->redis = \RedisLV4::connection();
    }

    /**
     * @param $handle
     * @param null $realm
     * @return null|IAssociation
     * @throws \openid\exceptions\ReplayAttackException
     * @throws \openid\exceptions\OpenIdInvalidRealmException
     */
    public function getAssociation($handle, $realm = null)
    {

        // check if association is on redis cache
        if ($this->redis->exists($handle)) {
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
                $success = $this->redis->setnx('lock.get.assoc.'. $handle . 1);
                if (!$success) { // only one time we could use this handle
                    throw new ReplayAttackException(sprintf(OpenIdErrorMessages::ReplayAttackPrivateAssociationAlreadyUsed, $handle));
                }
            }
            $assoc               = new OpenIdAssociation();
            $assoc->type         = $values[0];
            $assoc->mac_function = $values[1];
            $assoc->issued       = $values[2];
            $assoc->lifetime     = $values[3];
            $assoc->secret       = \hex2bin($values[4]);
            $realm = $values[5];
            if (!empty($realm))
                $assoc->realm = $realm;
            return $assoc;
        }
        // if not , check on db
        $assoc = OpenIdAssociation::where('identifier', '=', $handle)->first();

        if (!is_null($assoc)) {
            $issued_date = new DateTime($assoc->issued);
            if ($assoc->type == IAssociation::TypePrivate) {
                if (is_null($realm) || empty($realm) || $assoc->realm != $realm) {
                    throw new OpenIdInvalidRealmException(sprintf(OpenIdErrorMessages::InvalidPrivateAssociationMessage, $handle, $realm));
                }
                $success = $this->redis->setnx('lock.get.assoc.'. $handle, 1);
                if (!$success) {
                    throw new ReplayAttackException(sprintf(OpenIdErrorMessages::ReplayAttackPrivateAssociationAlreadyUsed, $handle));
                }
            }
            $life_time = $assoc->lifetime;
            $issued_date->add(new DateInterval('PT' . $life_time . 'S'));
            $now = new DateTime(gmdate("Y-m-d H:i:s", time()));
            if ($now > $issued_date) {
                $this->deleteAssociation($handle);
                $assoc = null;
            }
        }
        return $assoc;
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
     * @param IAssociation $association
     * @return bool
     */
    public function addAssociation($handle, $secret, $mac_function, $lifetime, $issued, $type, $realm = null)
    {

        $success = $this->redis->setnx('lock.add.assoc.' . $handle, 1);
        if (!$success) {
            throw new ReplayAttackException(sprintf(OpenIdErrorMessages::ReplayAttackPrivateAssociationAlreadyUsed, $handle));
        }

        $assoc = new OpenIdAssociation();
        if ($type == IAssociation::TypeSession) {
            $assoc->identifier = $handle;
            $assoc->secret = $secret;
            $assoc->type = $type;
            $assoc->mac_function = $mac_function;
            $assoc->lifetime = $lifetime;
            $assoc->issued = $issued;
            if (!is_null($realm))
                $assoc->realm = $realm;
            $assoc->Save();
        }

        if (is_null($realm))
            $realm = '';

        $this->redis->hmset($handle, array(
            "type"         => $type,
            "mac_function" => $mac_function,
            "issued"       => $issued,
            "lifetime"     => $lifetime,
            "secret"       => \bin2hex($secret),
            "realm"        => $realm));

        $this->redis->expire($handle, $lifetime);
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