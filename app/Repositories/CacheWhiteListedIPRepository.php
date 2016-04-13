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

use Models\IWhiteListedIPRepository;
use Models\WhiteListedIP;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
/**
 * Class CacheWhiteListedIPRepository
 * @package Repositories
 */
final class CacheWhiteListedIPRepository
    extends BaseCacheRepository implements IWhiteListedIPRepository
{

    public function __construct(EloquentWhiteListedIPRepository $repository)
    {
        $this->cache_base_key         = 'white_listed_ip';
        $this->cache_minutes_lifetime = Config::get("cache_regions.region_white_listed_ip_lifetime", 1140);
        parent::__construct($repository);
    }


    /**
     * @param string $ip
     * @return WhiteListedIP
     */
    function getByIp($ip)
    {
        return Cache::remember($this->cache_base_key.'_'.$ip, $this->cache_minutes_lifetime, function() use($ip) {
            return $this->repository->getByIp($ip);
        });
    }
}