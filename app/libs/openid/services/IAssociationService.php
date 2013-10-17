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
    public function addAssociation($handle,$secret,$type,$lifetime,$issued);

    /**
     * @param $handle
     * @return bool
     */
    public function deleteAssociation($handle);
}