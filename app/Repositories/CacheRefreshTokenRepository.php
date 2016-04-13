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

use Models\OAuth2\RefreshToken;
use OAuth2\Repositories\IRefreshTokenRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
/**
 * Class CacheRefreshTokenRepository
 * @package Repositories
 */
final class CacheRefreshTokenRepository extends AbstractCacheOAuth2TokenRepository implements IRefreshTokenRepository
{

    /**
     * CacheRefreshTokenRepository constructor.
     * @param EloquentRefreshTokenRepository $repository
     */
    public function __construct(EloquentRefreshTokenRepository $repository)
    {
        $this->cache_base_key         = 'refresh_token';
        $this->cache_minutes_lifetime =  Config::get("cache_regions.region_refresh_token_lifetime", 1140);
        parent::__construct($repository);
    }

    /**
     * @param $hashed_value
     * @return RefreshToken
     */
    function getByValue($hashed_value)
    {
        return Cache::remember($this->cache_base_key.'_'.$hashed_value, $this->cache_minutes_lifetime, function() use($hashed_value) {
            return $this->repository->getByValue($hashed_value);
        });
    }

}