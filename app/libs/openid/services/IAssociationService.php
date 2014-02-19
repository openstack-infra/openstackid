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
	 * @param IAssociation $association
	 * @return IAssociation
	 * @throws \openid\exceptions\ReplayAttackException
	 */
	public function addAssociation(IAssociation $association);

    /**
     * @param $handle
     * @return bool
     */
    public function deleteAssociation($handle);

}