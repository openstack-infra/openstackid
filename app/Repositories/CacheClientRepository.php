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

use OAuth2\Models\IClient;
use OAuth2\Repositories\IClientRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
/**
 * Class CacheClientRepository
 * @package Repositories
 */
final class CacheClientRepository extends BaseCacheRepository implements IClientRepository
{

    public function __construct(EloquentClientRepository $repository)
    {
        $this->cache_base_key         = 'client';
        $this->cache_minutes_lifetime = Config::get("cache_regions.region_client_lifetime", 1140);
        parent::__construct($repository);
    }

    /**
     * @param string $app_name
     * @return IClient
     */
    public function getByApplicationName($app_name)
    {
        return Cache::remember($this->cache_base_key.'_'.$app_name, $this->cache_minutes_lifetime, function() use($app_name) {
            return $this->repository->getByApplicationName($app_name);
        });
    }

    /**
     * @param string $client_id
     * @return IClient
     */
    public function getClientById($client_id)
    {
        return Cache::remember($this->cache_base_key.'_'.$client_id, $this->cache_minutes_lifetime, function() use($client_id) {
            return $this->repository->getClientById($client_id);
        });
    }

    /**
     * @param int $id
     * @return IClient
     */
    public function getClientByIdentifier($id)
    {
        return Cache::remember($this->cache_base_key.'_'.$id, $this->cache_minutes_lifetime, function() use($id) {
            return $this->repository->getClientByIdentifier($id);
        });
    }

    /**
     * @param string $origin
     * @return IClient
     */
    public function getByOrigin($origin)
    {
        return Cache::remember($this->cache_base_key.'_'.$origin, $this->cache_minutes_lifetime, function() use($origin) {
            return $this->repository->getByOrigin($origin);
        });
    }
}