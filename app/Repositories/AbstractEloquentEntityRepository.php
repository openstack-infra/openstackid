<?php namespace Repositories;
/**
 * Copyright 2015 OpenStack Foundation
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
use Utils\Model\IEntity;
use Utils\Db\IBaseRepository;
/**
 * Class AbstractEloquentEntityRepository
 * @package Repositories
 */
abstract class AbstractEloquentEntityRepository implements IBaseRepository
{
    /**
     * @var IEntity
     */
    protected $entity;

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr = 1, $page_size = 10, array $filters = array(), array $fields = array('*'))
    {
        return $this->entity->Filter($filters)->paginate($page_size, $fields, $pageName = 'Page', $page_nbr);
    }

    /**
     * @param IEntity $entity
     * @return bool
     */
    public function update(IEntity $entity)
    {
        return $entity->save();
    }

    /**
     * @param IEntity $entity
     * @return bool
     */
    public function add(IEntity $entity)
    {
        return $entity->save();
    }

    /**
     * @param IEntity $entity
     * @return bool
     */
    public function delete(IEntity $entity)
    {
        return $entity->delete();
    }

    /**
     * @param int $id
     * @return IEntity
     */
    public function get($id)
    {
        return $this->entity->find($id);
    }
}