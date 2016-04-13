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
/**
 * Interface IApiService
 * @package OAuth2\Services
 */
interface IApiService {

    /**
     * @param $api_id
     * @return IApi
     */
    public function get($api_id);

    /**
     * @param $api_name
     * @return IApi
     */
    public function getByName($api_name);

    /**
     * @param $id
     * @return bool
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
     * @throws InvalidApi
     */
    public function update($id, array $params);
    /**
     * @param IApi $api
     * @return void
     */
    public function save(IApi $api);

    /**
     * @param $id
     * @param $active
     * @return bool
     */
    public function setStatus($id, $active);

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr=1,$page_size=10,array $filters=array(),array $fields=array('*'));

} 