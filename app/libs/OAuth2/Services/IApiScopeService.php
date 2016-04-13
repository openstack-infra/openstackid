<?php namespace OAuth2\Services;
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
use OAuth2\Models\IApiScope;
use OAuth2\Exceptions\InvalidApiScope;
/**
 * Interface IApiScopeService
 * @package OAuth2\Services
 */
interface IApiScopeService
{
    /**
     * gets an api scope by id
     * @param int $id id of api scope
     * @return IApiScope
     */
    public function get($id);

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr=1,$page_size=10, array $filters=array(), array $fields=array('*'));

    /**
     * @param IApiScope $scope
     * @return bool
     */
    public function save(IApiScope $scope);

    /**
     * @param $id
     * @param array $params
     * @return bool
     * @throws InvalidApiScope
     */
    public function update($id, array $params);

    /**
     * sets api scope status (active/deactivated)
     * @param $id
     * @param bool $status status (active/non active)
     * @return void
     */
    public function setStatus($id,$status);

    /**
     * deletes an api scope
     * @param $id id of api scope
     * @return bool
     */
    public function delete($id);

    /**
     * Creates a new api scope instance
     * @param $name
     * @param $short_description
     * @param $description
     * @param $active
     * @param $default
     * @param $system
     * @param $api_id
     * @param $assigned_by_groups
     * @return IApiScope
     */
    public function add($name, $short_description, $description, $active, $default, $system, $api_id, $assigned_by_groups);

    /**
     * @param array $scopes_names
     * @return mixed
     */
    public function getScopesByName(array $scopes_names);

    /**
     * Given an array of scopes names, gets friendly names for given ones
     * @param array $scopes_names
     * @return mixed
     */
    public function getFriendlyScopesByName(array $scopes_names);

    /**
     * Get all active scopes (system/non system ones)
     * @param bool $system
     * @param bool $assigned_by_groups
     * @return array|mixed
     */
    public function getAvailableScopes($system = false, $assigned_by_groups = false);

    /**
     * Given a set of scopes names, retrieves a list of resource server owners
     * @param array $scopes_names
     * @return mixed
     */
    public function getAudienceByScopeNames(array $scopes_names);

    /**
     * gets audience string for a given scopes sets (resource servers)
     * @param array $scopes_names
     * @return mixed
     */
    public function getStrAudienceByScopeNames(array $scopes_names);

    /**
     * gets a list of default scopes
     * @return mixed
     */
    public function getDefaultScopes();

    /**
     * @return mixed
     */
    public function getAssignedByGroups();
} 