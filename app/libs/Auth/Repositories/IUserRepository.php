<?php namespace Auth\Repositories;
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
/**
 * Interface IUserRepository
 * @package Auth\Repositories
 */
interface IUserRepository extends IBaseRepository
{

    /**
     * @param $external_id
     * @return User
     */
    public function getByExternalId($external_id);

    /**
     * @param $filters
     * @return array
     */
    public function getByCriteria($filters);

    /**
     * @param $filters
     * @return User
     */
    public function getOneByCriteria($filters);


    /**
     * @param array $filters
     * @return int
     */
    public function getCount(array $filters = array());

    /**
     * @param mixed $identifier
     * @param string $token
     * @return User
     */
    public function getByToken($identifier, $token);


    /**
     * @param string $term
     * @return array
     */
    public function getByEmailOrName($term);

    /**
     * @param string $user_identifier
     * @return User
     */
    public function getByIdentifier($user_identifier);
} 