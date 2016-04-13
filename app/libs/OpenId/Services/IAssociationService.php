<?php namespace OpenId\Services;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use OpenId\Models\IAssociation;
use OpenId\Exceptions\ReplayAttackException;
use OpenId\Exceptions\OpenIdInvalidRealmException;
/**
 * Interface IAssociationService
 * @package OpenId\Services
 */
interface IAssociationService
{
    /** gets a given association by handle, and if association exists and its type is private, then lock it
     *  to prevent subsequent usage ( private association could be used once)
     * @param $handle
     * @param null $realm
     * @return null|IAssociation
     * @throws ReplayAttackException
     * @throws OpenIdInvalidRealmException
     */
    public function getAssociation($handle, $realm = null);

	/**
	 * @param IAssociation $association
	 * @return IAssociation
	 * @throws ReplayAttackException
	 */
	public function addAssociation(IAssociation $association);

    /**
     * @param $handle
     * @return bool
     */
    public function deleteAssociation($handle);

}