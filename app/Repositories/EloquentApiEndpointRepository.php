<?php namespace repositories;
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
use Models\OAuth2\ApiEndpoint;
use OAuth2\Repositories\IApiEndpointRepository;
use OAuth2\Models\IApiEndpoint;
/**
 * Class EloquentApiEndpointRepository
 * @package repositories
 */
class EloquentApiEndpointRepository extends AbstractEloquentEntityRepository implements IApiEndpointRepository
{
    /**
     * @param ApiEndpoint $endpoint
     */
    public function __construct(ApiEndpoint $endpoint)
    {
        $this->entity = $endpoint;
    }

    /**
     * @param string $url
     * @param string $http_method
     * @return IApiEndpoint
     */
    public function getApiEndpointByUrlAndMethod($url, $http_method)
    {
        return $this->entity->Filter(array(
            array(
                'name' => 'route',
                'op' => '=',
                'value' => $url
            ),
            array(
                'name' => 'http_method',
                'op' => '=',
                'value' => $http_method
            )
        ))->first();
    }

}