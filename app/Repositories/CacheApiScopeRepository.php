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

use OAuth2\Models\IApiScope;
use OAuth2\Repositories\IApiScopeRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
/**
 * Class CacheApiScopeRepository
 * @package Repositories
 */
final class CacheApiScopeRepository extends BaseCacheRepository implements IApiScopeRepository
{

    public function __construct(EloquentApiScopeRepository $repository)
    {
        $this->cache_base_key         = 'api_scope';
        $this->cache_minutes_lifetime = $this->cache_minutes_lifetime = Config::get("cache_regions.region_api_scope_lifetime", 1140);
        parent::__construct($repository);
    }

    /**
     * @param array $scopes_names
     * @return IApiScope[]
     */
    public function getByName(array $scopes_names)
    {
        return Cache::remember($this->cache_base_key.'_'.join('_', $scopes_names), $this->cache_minutes_lifetime, function() use($scopes_names) {
            return $this->repository->getByName($scopes_names);
        });
    }

    /**
     * @return IApiScope[]
     */
    public function getDefaults()
    {
        return Cache::remember($this->cache_base_key.'_defaults', $this->cache_minutes_lifetime, function() {
            return $this->repository->getDefaults();
        });
    }

    /**
     * @return IApiScope[]
     */
    public function getActives()
    {
        return Cache::remember($this->cache_base_key.'_actives', $this->cache_minutes_lifetime, function() {
            return $this->repository->getActives();
        });
    }

    /**
     * @return IApiScope[]
     */
    public function getAssignableByGroups()
    {
        return Cache::remember($this->cache_base_key.'_assignables_by_groups', $this->cache_minutes_lifetime, function() {
            return $this->repository->getAssignableByGroups();
        });
    }
}