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

use OAuth2\Exceptions\InvalidResourceServer;
use OAuth2\Models\IResourceServer;
use Utils\Exceptions\EntityNotFoundException;

/**
 * Interface IResourceServerService
 * @package OAuth2\Services
 */
interface IResourceServerService {

    /**
     * @param string $host
     * @param string $ips
     * @param string $friendly_name
     * @param bool $active
     * @return IResourceServer
     * @throws InvalidResourceServer
     */
    public function add($host, $ips, $friendly_name, $active);

    /**
     * @param int $id
     * @param array $params
     * @return bool
     * @throws InvalidResourceServer
     * @throws EntityNotFoundException
     */
    public function update($id, array $params);

    /**
     * @param int $id
     * @param bool $active
     * @return bool
     * @throws EntityNotFoundException
     */
    public function setStatus($id , $active);

    /**
     * @param int $id
     * @return bool
     * @throws EntityNotFoundException
     */
    public function delete($id);

    /**
     * @param int $id
     * @return string
     * @throws EntityNotFoundException
     */
    public function regenerateClientSecret($id);
}