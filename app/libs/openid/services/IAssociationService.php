<?php

namespace openid\services;

use openid\model\IAssociation;

/**
 * Interface IAssociationService
 * @package openid\services
 */
interface IAssociationService
{
    /** gets a given association by handle, and if association exists and its type is private, then lock it
     *  to prevent subsequent usage ( private association could be used once)
     * @param $handle
     * @param null $realm
     * @return null|IAssociation
     * @throws \openid\exceptions\ReplayAttackException
     * @throws \openid\exceptions\OpenIdInvalidRealmException
     */
    public function getAssociation($handle, $realm = null);

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
    public function addAssociation($handle, $secret, $mac_function, $lifetime, $issued, $type, $realm);

    /**
     * @param $handle
     * @return bool
     */
    public function deleteAssociation($handle);

    /**
     * For verifying signatures an OP MUST only use private associations and MUST NOT
     * use associations that have shared keys. If the verification request contains a handle
     * for a shared association, it means the Relying Party no longer knows the shared secret,
     * or an entity other than the RP (e.g. an attacker) has established this association with the OP.
     * @param $handle
     * @return mixed
     */
    public function getAssociationType($handle);
}