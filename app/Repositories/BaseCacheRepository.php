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
use Utils\Db\IBaseRepository;
use Utils\Model\IEntity;
use Illuminate\Support\Facades\Cache;
/**
 * Class BaseCacheRepository
 * @package Repositories
 */
abstract class BaseCacheRepository implements IBaseRepository
{

    /**
     * @var IBaseRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $cache_base_key;

    /**
     * @var int
     */
    protected $cache_minutes_lifetime;

    /**
     * BaseCacheRepository constructor.
     * @param IBaseRepository $repository
     */
    public function __construct(IBaseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param int $id
     * @return IEntity
     */
    public function get($id)
    {
        return Cache::remember($this->cache_base_key.'_'.$id, $this->cache_minutes_lifetime, function() use($id) {
            return $this->repository->get($id);
        });
    }

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr = 1, $page_size = 10, array $filters = [], array $fields = ['*'])
    {
        return $this->repository->getAll($page_nbr, $page_size, $filters, $fields);
    }

    /**
     * @param IEntity $entity
     * @return bool
     */
    public function update(IEntity $entity)
    {
        return $this->update($entity);
    }

    /**
     * @param IEntity $entity
     * @return bool
     */
    public function add(IEntity $entity)
    {
        return $this->repository->add($entity);
    }

    /**
     * @param IEntity $entity
     * @return bool
     */
    public function delete(IEntity $entity)
    {
        return $this->repository->delete($entity);
    }

}