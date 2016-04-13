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
use OAuth2\Models\IApi;
use OAuth2\Exceptions\InvalidApi;
use Utils\Exceptions\EntityNotFoundException;
/**
 * Interface IApiService
 * @package OAuth2\Services
 */
interface IApiService {

    /**
     * @param int $id
     * @return bool
     * @throws EntityNotFoundException
     */
    public function delete($id);

    /**
     * @param $name
     * @param $description
     * @param $active
     * @param $resource_server_id
     * @return IApi
     */
    public function add($name, $description, $active, $resource_server_id);

    /**
     * @param $id
     * @param array $params
     * @return bool
     * @throws EntityNotFoundException
     * @throws InvalidApi
     */
    public function update($id, array $params);

    /**
     * @param int $id
     * @param bool $active
     * @return bool
     * @throws EntityNotFoundException
     */
    public function setStatus($id, $active);

} 