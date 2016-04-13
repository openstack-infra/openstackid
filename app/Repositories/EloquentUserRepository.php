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
use Utils\Services\ILogService;
use Models\Member;
/**
 * Class EloquentUserRepository
 * @package repositories
 */
final class EloquentUserRepository extends AbstractEloquentEntityRepository implements IUserRepository
{

    /**
     * @var ILogService
     */
    private $log_service;

    /**
     * EloquentUserRepository constructor.
     * @param User $user
     * @param ILogService $log_service
     */
    public function __construct(User $user, ILogService $log_service)
    {
        $this->entity      = $user;
        $this->log_service = $log_service;
    }

    /**
     * @param $id
     * @return User
     */
    public function get($id)
    {
        return $this->entity->find($id);
    }

    public function getByCriteria($filters)
    {
        return $this->entity->Filter($filters)->get();
    }

    public function getOneByCriteria($filters)
    {
        return $this->entity->Filter($filters)->first();
    }

     /**
     * @param array $filters
     * @return int
     */
    public function getCount(array $filters = array())
    {
        return $this->entity->Filter($filters)->count();
    }

    /**
     * @param $external_id
     * @return User
     */
    public function getByExternalId($external_id)
    {
        return $this->entity->where('external_identifier', '=', $external_id)->first();
    }

    /**
     * @param mixed $identifier
     * @param string $token
     * @return User
     */
    public function getByToken($identifier, $token)
    {
        return $this->entity
            ->where('external_identifier', '=', $identifier)
            ->where('remember_token', '=',$token)->first();
    }

    /**
     * @param string $term
     * @return array
     */
    public function getByEmailOrName($term)
    {
        $list    = array();
        $members = Member::where('Email', 'like', '%'.$term.'%')->paginate(10);
        foreach($members->getItems() as $m)
        {
            $user = $this->getByExternalId(intval($m->ID));
            if(!is_null($user))
                array_push($list, $user);
        }
        return $list;
    }

    /**
     * @param string $user_identifier
     * @return User
     */
    public function getByIdentifier($user_identifier)
    {
        return $this->entity->where('identifier', '=', $user_identifier)->first();
    }
}