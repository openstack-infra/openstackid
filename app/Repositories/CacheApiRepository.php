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

use OAuth2\Models\IApi;
use OAuth2\Repositories\IApiRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
/**
 * Class CacheApiRepository
 * @package Repositories
 */
final class CacheApiRepository extends BaseCacheRepository implements IApiRepository
{

    public function __construct(EloquentApiRepository $repository)
    {
        $this->cache_base_key         = 'api';
        $this->cache_minutes_lifetime = Config::get("cache_regions.region_api_lifetime", 1140);
        parent::__construct($repository);
    }

    /**
     * @param string $api_name
     * @return IApi
     */
    public function getByName($api_name)
    {
        return Cache::remember($this->cache_base_key.'_'.$api_name, $this->cache_minutes_lifetime, function() use($api_name) {
            return $this->repository->getByName($api_name);
        });
    }

    /**
     * @param string $api_name
     * @param int $resource_server_id
     * @return IApi
     */
    public function getByNameAndResourceServer($api_name, $resource_server_id)
    {
        return Cache::remember($this->cache_base_key.'_'.$api_name.'_'.$resource_server_id, $this->cache_minutes_lifetime, function() use($api_name, $resource_server_id) {
            return $this->repository->getByNameAndResourceServer($api_name, $resource_server_id);
        });
    }
}