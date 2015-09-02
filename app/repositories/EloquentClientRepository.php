<?php
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

namespace repositories;

use oauth2\models\IClient;
use oauth2\repositories\IClientRepository;
use oauth2\repositories\id;
use Client;
use utils\services\ILogService;
/**
 * Class EloquentClientRepository
 * @package repositories
 */
final class EloquentClientRepository implements IClientRepository {

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ILogService
     */
    private $log_service;

    /**
     * @param Client $client
     * @param ILogService $log_service
     */
    public function __construct(Client $client, ILogService $log_service){
        $this->client  = $client;
        $this->log_service = $log_service;
    }

    /**
     * @param int id
     * @return IClient
     */
    public function get($id)
    {
        return $this->client->find($id);
    }

    /**
     * @param IClient $client
     * @return void
     */
    public function add(IClient $client)
    {
         $client->save();
    }

    /**
     * @param IClient $client
     * @return void
     */
    public function delete(IClient $client)
    {
        $client->delete();
    }
}