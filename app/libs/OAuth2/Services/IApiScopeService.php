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
use Utils\Exceptions\EntityNotFoundException;

/**
 * Interface IApiScopeService
 * @package OAuth2\Services
 */
interface IApiScopeService
{
    /**
     * @param $id
     * @param array $params
     * @return bool
     * @throws InvalidApiScope
     * @throws EntityNotFoundException
     */
    public function update($id, array $params);

    /**
     * @param int $id
     * @param bool $active
     * @return bool
     * @throws EntityNotFoundException
     */
    public function setStatus($id, $active);

    /**
     * @param int $id
     * @return bool
     * @throws EntityNotFoundException
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
     * @return mixed
     */
    public function getAssignedByGroups();
} 