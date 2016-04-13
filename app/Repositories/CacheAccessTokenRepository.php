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

use Illuminate\Support\Facades\Config;
use Models\OAuth2\AccessToken;
use OAuth2\Repositories\IAccessTokenRepository;
use Illuminate\Support\Facades\Cache;

/**
 * Class CacheAccessTokenRepository
 * @package Repositories
 */
final class CacheAccessTokenRepository extends AbstractCacheOAuth2TokenRepository  implements IAccessTokenRepository
{

    /**
     * CacheAccessTokenRepository constructor.
     * @param EloquentAccessTokenRepository $repository
     */
    public function __construct(EloquentAccessTokenRepository $repository)
    {
        $this->cache_base_key         = 'access_token';
        $this->cache_minutes_lifetime = Config::get("cache_regions.region_access_token_lifetime", 1140);
        parent::__construct($repository);
    }

    /**
     * @param string $hashed_value
     * @return AccessToken
     */
    function getByValue($hashed_value)
    {
        return Cache::remember($this->cache_base_key.'_'.$hashed_value, $this->cache_minutes_lifetime, function() use($hashed_value) {
            return $this->repository->getByValue($hashed_value);
        });
    }

    /**
     * @param string $hashed_value
     * @return AccessToken
     */
    function getByAuthCode($hashed_value)
    {
        return Cache::remember($this->cache_base_key.'_'.$hashed_value, $this->cache_minutes_lifetime, function() use($hashed_value) {
            return $this->repository->getByAuthCode($hashed_value);
        });
    }

    /**
     * @param int $refresh_token_id
     * @return AccessToken[]
     */
    function getByRefreshToken($refresh_token_id)
    {
        return $this->repository->getByRefreshToken($refresh_token_id);
    }

}