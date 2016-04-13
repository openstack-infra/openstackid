<?php namespace OAuth2\Repositories;
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
use Utils\Db\IBaseRepository;
/**
 * Interface IApiRepository
 * @package OAuth2\Repositories
 */
interface IApiRepository extends IBaseRepository
{
    /**
     * @param string $api_name
     * @return IApi
     */
    public function getByName($api_name);

    /**
     * @param string $api_name
     * @param int $resource_server_id
     * @return IApi
     */
    public function getByNameAndResourceServer($api_name, $resource_server_id);
}