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

use OAuth2\Models\IApiEndpoint;
use OAuth2\Exceptions\InvalidApiScope;
use OAuth2\Exceptions\InvalidApiEndpoint;
use Utils\Exceptions\EntityNotFoundException;

/**
 * Interface IApiEndpointService
 * @package OAuth2\Services
 */
interface IApiEndpointService {

    /**
     * Adds a new api endpoint to an existent api
     * @param string $name
     * @param string $description
     * @param boolean $active
     * @param boolean $allow_cors
     * @param string $route
     * @param string $http_method
     * @param int $api_id
     * @param int $rate_limit
     * @return IApiEndpoint
     */
    public function add($name, $description, $active, $allow_cors, $route, $http_method, $api_id, $rate_limit);

    /**
     * Adds a new required scope to a given api endpoint,
     * given scope must belongs to owner api of the given endpoint
     * @param int $api_endpoint_id
     * @param int $scope_id
     * @return boolean
     * @throws InvalidApiScope
     * @throws InvalidApiEndpoint
     * @throws EntityNotFoundException
     */
    public function addRequiredScope($api_endpoint_id, $scope_id);

    /**
     * Remove a required scope to a given api endpoint,
     * given scope must belongs to owner api of the given endpoint
     * @param int $api_endpoint_id
     * @param int $scope_id
     * @return boolean
     * @throws InvalidApiScope
     * @throws InvalidApiEndpoint
     * @throws EntityNotFoundException
     */
    public function removeRequiredScope($api_endpoint_id, $scope_id);

    /**
     * deletes a given api endpoint
     * @param int $id
     * @return boolean
     * @throws EntityNotFoundException
     */
    public function delete($id);

    /**
     * @param int $id
     * @return mixed
     */
    public function get($id);

    /**
     * @param int $id
     * @param array $params
     * @return bool
     * @throws EntityNotFoundException
     * @throws InvalidApiEndpoint
     */
    public function update($id, array $params);

    /**
     * @param int $id
     * @param boolean $active
     * @throws EntityNotFoundException
     * @return boolean
     */
    public function setStatus($id, $active);

} 