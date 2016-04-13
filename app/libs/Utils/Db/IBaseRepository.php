<?php namespace Utils\Db;
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
/**
 * Interface IBaseRepository
 * @package Utils\Db
 */
interface IBaseRepository
{
    /**
     * @param int $id
     * @return IEntity
     */
    public function get($id);

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr = 1, $page_size = 10, array $filters = [], array $fields = ['*']);

    /**
     * @param IEntity $entity
     * @return bool
     */
    public function update(IEntity $entity);

    /**
     * @param IEntity $entity
     * @return bool
     */
    public function add(IEntity $entity);


    /**
     * @param IEntity $entity
     * @return bool
     */
    public function delete(IEntity $entity);

}