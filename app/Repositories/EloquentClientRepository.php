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
use OAuth2\Models\IClient;
use Models\OAuth2\Client;
use OAuth2\Repositories\IClientRepository;
use Utils\Services\ILogService;
/**
 * Class EloquentClientRepository
 * @package Repositories
 */
final class EloquentClientRepository extends AbstractEloquentEntityRepository implements IClientRepository {

    /**
     * @var ILogService
     */
    private $log_service;

    /**
     * @param Client $client
     * @param ILogService $log_service
     */
    public function __construct(Client $client, ILogService $log_service){
        $this->entity      = $client;
        $this->log_service = $log_service;
    }

    /**
     * @param string $app_name
     * @return IClient
     */
    public function getByApplicationName($app_name)
    {
        return $this->entity->where('app_name', '=', trim($app_name))->first();
    }

    /**
     * @param string $client_id
     * @return IClient
     */
    public function getClientById($client_id)
    {
        return $this->entity->where('client_id', '=', $client_id)->first();
    }

    /**
     * @param int $id
     * @return IClient
     */
    public function getClientByIdentifier($id)
    {
        return $this->entity->where('id', '=', $id)->first();
    }

    /**
     * @param string $origin
     * @return IClient
     */
    public function getByOrigin($origin)
    {
        return $this->entity->where('allowed_origins', 'like', '%'.$origin.'%')->first();
    }
}