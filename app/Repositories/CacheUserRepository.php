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
use Auth\Repositories\IUserRepository;
use Auth\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
/**
 * Class CacheUserRepository
 * @package Repositories
 */
final class CacheUserRepository extends BaseCacheRepository  implements IUserRepository
{

    public function __construct(EloquentUserRepository $repository)
    {
        $this->cache_base_key         = 'user';
        $this->cache_minutes_lifetime = Config::get("cache_regions.region_users_lifetime", 30);
        parent::__construct($repository);
    }

    /**
     * @param $external_id
     * @return User
     */
    public function getByExternalId($external_id)
    {
        return Cache::remember($this->cache_base_key.'_'.$external_id, $this->cache_minutes_lifetime, function() use($external_id) {
            return $this->repository->getByExternalId($external_id);
        });
    }

    /**
     * @param $filters
     * @return array
     */
    public function getByCriteria($filters)
    {
        return $this->repository->getByCriteria($filters);
    }

    /**
     * @param $filters
     * @return User
     */
    public function getOneByCriteria($filters)
    {
        return $this->repository->getOneByCriteria($filters);
    }

    /**
     * @param array $filters
     * @return int
     */
    public function getCount(array $filters = array())
    {
        return $this->repository->getCount($filters);
    }

    /**
     * @param mixed $identifier
     * @param string $token
     * @return User
     */
    public function getByToken($identifier, $token)
    {
        return $this->repository->getByToken($identifier, $token);
    }

    /**
     * @param string $term
     * @return array
     */
    public function getByEmailOrName($term)
    {
        return Cache::remember($this->cache_base_key.'_'.$term, $this->cache_minutes_lifetime, function() use($term) {
            return $this->repository->getByEmailOrName($term);
        });
    }

    /**
     * @param string $user_identifier
     * @return User
     */
    public function getByIdentifier($user_identifier)
    {
        return Cache::remember($this->cache_base_key.'_'.$user_identifier, $this->cache_minutes_lifetime, function() use($user_identifier) {
            return $this->repository->getByIdentifier($user_identifier);
        });
    }

    /**
     * @param $id
     * @return User
     */
    public function get($id)
    {
        return Cache::remember($this->cache_base_key.'_'.$id, $this->cache_minutes_lifetime, function() use($id) {
            return $this->repository->get($id);
        });
    }
}