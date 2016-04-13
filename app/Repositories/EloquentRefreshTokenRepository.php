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
use Models\OAuth2\RefreshToken;
use OAuth2\Repositories\IRefreshTokenRepository;
use Utils\Services\ILogService;

/**
 * Class EloquentRefreshTokenRepository
 * @package Repositories
 */
final class EloquentRefreshTokenRepository extends AbstractEloquentEntityRepository implements IRefreshTokenRepository
{
    /**
     * @param RefreshToken $entity
     * @param ILogService $log_service
     */
    public function __construct(RefreshToken $entity, ILogService $log_service){
        $this->entity      = $entity;
        $this->log_service = $log_service;
    }

    /**
     * @param int $client_identifier
     * @param int $page_nbr
     * @param int $page_size
     * @return LengthAwarePaginator
     */
    public function getAllByClientIdentifier($client_identifier, $page_nbr = 1, $page_size = 10){
        return $this->getAll($page_nbr, $page_size, [['name' => 'client_id', 'op' => '=','value' => $client_identifier]]);
    }

    /**
     * @param int $user_id
     * @param int $page_nbr
     * @param int $page_size
     * @return LengthAwarePaginator
     */
    public function getAllByUserId($user_id, $page_nbr = 1, $page_size = 10){
        return $this->getAll($page_nbr, $page_size, [['name' => 'user_id', 'op' => '=','value' => $user_id]]);
    }

    /**
     * @param int $client_identifier
     * @param int $page_nbr
     * @param int $page_size
     * @return LengthAwarePaginator
     */
    function getAllValidByClientIdentifier($client_identifier, $page_nbr = 1, $page_size = 10)
    {
        return $this->getAll($page_nbr, $page_size, [
            ['name' => 'client_id', 'op' => '=','value' => $client_identifier ],
            ['raw'  => 'DATE_ADD(created_at, INTERVAL lifetime second) >= UTC_TIMESTAMP()'],
        ]);

    }

    /**
     * @param int $user_id
     * @param int $page_nbr
     * @param int $page_size
     * @return LengthAwarePaginator
     */
    function getAllValidByUserId($user_id, $page_nbr = 1, $page_size = 10)
    {
        return $this->getAll($page_nbr, $page_size, [
            ['name' => 'user_id', 'op' => '=','value' => $user_id ],
            ['raw'  => 'DATE_ADD(created_at, INTERVAL lifetime second) >= UTC_TIMESTAMP()'],
        ]);
    }
}