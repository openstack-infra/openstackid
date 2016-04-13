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
use OAuth2\Models\IApiEndpoint;
use Utils\Db\IBaseRepository;
/**
 * Interface IApiEndpointRepository
 * @package OAuth2\Repositories
 */
interface IApiEndpointRepository extends IBaseRepository
{
    /**
     * @param string $url
     * @param string $http_method
     * @return IApiEndpoint
     */
    public function getApiEndpointByUrlAndMethod($url, $http_method);

    /**
     * @param string $url
     * @param string $http_method
     * @param int $api_id
     * @return IApiEndpoint
     */
    public function getApiEndpointByUrlAndMethodAndApi($url, $http_method, $api_id);

    /**
     * @param string $url
     * @return IApiEndpoint
     */
    public function getApiEndpointByUrl($url);

}