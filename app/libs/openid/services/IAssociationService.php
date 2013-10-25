<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/17/13
 * Time: 10:39 AM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\services;
use openid\model\IAssociation;

interface IAssociationService {
    /**
     * @param $handle
     * @return IAssociation
     */
    public function getAssociation($handle);

    /**
     * @param IAssociation $association
     * @return bool
     */
    public function addAssociation($handle,$secret,$mac_function,$lifetime,$issued,$type);

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