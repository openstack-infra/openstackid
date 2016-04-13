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

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Utils\Model\IEntity;

/**
 * Class AbstractCacheOAuth2TokenRepository
 * @package Repositories
 */
abstract class AbstractCacheOAuth2TokenRepository extends BaseCacheRepository
{
    function add(IEntity $entity)
    {
        Cache::forget($this->cache_base_key.'_'.$entity->value);
        return parent::add($entity);
    }

    function update(IEntity $entity)
    {
        Cache::forget($this->cache_base_key.'_'.$entity->value);
        return parent::update($entity);
    }

    function delete(IEntity $entity)
    {
        Cache::forget($this->cache_base_key.'_'.$entity->value);
        return parent::delete($entity);
    }

    /**
     * @param int $client_identifier
     * @param int $page_nbr
     * @param int $page_size
     * @return LengthAwarePaginator
     */
    function getAllByClientIdentifier($client_identifier, $page_nbr = 1, $page_size = 10)
    {
        return $this->repository->getAllByClientIdentifier($client_identifier, $page_nbr, $page_size);
    }

    /**
     * @param int $client_identifier
     * @param int $page_nbr
     * @param int $page_size
     * @return LengthAwarePaginator
     */
    function getAllValidByClientIdentifier($client_identifier, $page_nbr = 1, $page_size = 10)
    {
        return $this->getAllValidByClientIdentifier($client_identifier, $page_nbr, $page_size );
    }

    /**
     * @param int $user_id
     * @param int $page_nbr
     * @param int $page_size
     * @return LengthAwarePaginator
     */
    function getAllByUserId($user_id, $page_nbr = 1, $page_size = 10)
    {
        return $this->repository->getAllByUserId($user_id, $page_nbr, $page_size);
    }

    /**
     * @param int $user_id
     * @param int $page_nbr
     * @param int $page_size
     * @return LengthAwarePaginator
     */
    function getAllValidByUserId($user_id, $page_nbr = 1, $page_size = 10)
    {
        return $this->getAllValidByUserId($user_id, $page_nbr, $page_size);
    }
}