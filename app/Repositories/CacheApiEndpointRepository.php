<?php namespace Repositories;
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
use OAuth2\Repositories\IApiEndpointRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
/**
 * Class CacheApiEndpointRepository
 * @package Repositories
 */
final class CacheApiEndpointRepository extends BaseCacheRepository implements IApiEndpointRepository
{

    public function __construct(EloquentApiEndpointRepository $repository)
    {
        $this->cache_base_key         = 'api_endpoint';
        $this->cache_minutes_lifetime = Config::get("cache_regions.region_api_endpoint_lifetime", 1140);
        parent::__construct($repository);
    }

    /**
     * @param string $url
     * @param string $http_method
     * @return IApiEndpoint
     */
    public function getApiEndpointByUrlAndMethod($url, $http_method)
    {
        return Cache::remember($this->cache_base_key.'_'.$url.'_'.$http_method, $this->cache_minutes_lifetime, function() use($url, $http_method) {
            return $this->repository->getApiEndpointByUrlAndMethod($url, $http_method);
        });
    }

    /**
     * @param string $url
     * @param string $http_method
     * @param int $api_id
     * @return IApiEndpoint
     */
    public function getApiEndpointByUrlAndMethodAndApi($url, $http_method, $api_id)
    {
        return Cache::remember($this->cache_base_key.'_'.$url.'_'.$http_method.'_'.$api_id, $this->cache_minutes_lifetime, function() use($url, $http_method, $api_id) {
            return $this->repository->getApiEndpointByUrlAndMethodAndApi($url, $http_method, $api_id);
        });
    }

    /**
     * @param string $url
     * @return IApiEndpoint
     */
    public function getApiEndpointByUrl($url)
    {
        return Cache::remember($this->cache_base_key.'_'.$url, $this->cache_minutes_lifetime, function() use($url) {
            return $this->repository->getApiEndpointByUrl($url);
        });
    }
}