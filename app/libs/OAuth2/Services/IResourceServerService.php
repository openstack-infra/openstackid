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
/**
 * Interface IResourceServerService
 * @package OAuth2\Services
 */
interface IResourceServerService {

    /**
     * get a resource server by id
     * @param $id id of resource server
     * @return IResourceServer
     */
    public function get($id);

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr = 1,$page_size = 10, array $filters = array(), array $fields=array('*'));

    /**
     * @param IResourceServer $resource_server
     * @return bool
     */
    public function save(IResourceServer $resource_server);

    /**
     * @param $id
     * @param array $params
     * @return bool
     * @throws InvalidResourceServer
     */
    public function update($id, array $params);

    /**
     * sets resource server status (active/deactivated)
     * @param $id id of resource server
     * @param bool $status status (active/non active)
     * @return void
     */
    public function setStatus($id,$status);

    /**
     * deletes a resource server
     * @param $id id of resource server
     * @return bool
     */
    public function delete($id);


    /** Creates a new resource server instance
     * @param $host
     * @param $ips
     * @param $friendly_name
     * @param bool $active
     * @return IResourceServer
     */
    public function add($host, $ips, $friendly_name, $active);


    /**
     * @param $id resource server id
     * @return string
     */
    public function regenerateClientSecret($id);

    /**
     * @param string $ip
     * @return IResourceServer
     */
    public function getByIPAddress($ip);
} 