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
use OAuth2\Models\IResourceServer;
use OAuth2\Repositories\IResourceServerRepository;
use Utils\Model\IEntity;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
/**
 * Class CacheResourceServerRepository
 * @package Repositories
 */
final class CacheResourceServerRepository extends BaseCacheRepository implements IResourceServerRepository
{

    public function __construct(EloquentResourceServerRepository $repository)
    {
        $this->cache_base_key         = 'resource_server';
        $this->cache_minutes_lifetime = Config::get("cache_regions.region_resource_server_lifetime", 1140);
        parent::__construct($repository);
    }

    /**
     * @param string $host
     * @return IResourceServer
     */
    public function getByHost($host)
    {
        return Cache::remember($this->cache_base_key.'_'.$host, $this->cache_minutes_lifetime, function() use($host) {
            return $this->repository->getByHost($host);
        });
    }

    /**
     * @param string $ip
     * @return IResourceServer
     */
    public function getByIp($ip)
    {
        return Cache::remember($this->cache_base_key.'_'.$ip, $this->cache_minutes_lifetime, function() use($ip) {
            return $this->repository->getByIp($ip);
        });
    }

    /**
     * @param string $name
     * @return IResourceServer
     */
    public function getByFriendlyName($name)
    {
        return Cache::remember($this->cache_base_key.'_'.$name, $this->cache_minutes_lifetime, function() use($name) {
            return $this->repository->getByFriendlyName($name);
        });
    }

    /**
     * @param array $audience
     * @param string $ip
     * @return IResourceServer
     */
    public function getByAudienceAndIpAndActive(array $audience, $ip)
    {
        return Cache::remember($this->cache_base_key.'_'.join("_", $audience).'_'.$ip, $this->cache_minutes_lifetime, function() use($audience, $ip) {
            return $this->repository->getByAudienceAndIpAndActive($audience, $ip);
        });
    }
}