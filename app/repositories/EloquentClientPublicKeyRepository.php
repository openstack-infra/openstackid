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

use oauth2\models\IClientPublicKey;
use oauth2\repositories\IClientPublicKeyRepository;
use ClientPublicKey;
use utils\services\ILogService;
/**
 * Class EloquentClientPublicKeyRepository
 * @package repositories
 */
final class EloquentClientPublicKeyRepository implements IClientPublicKeyRepository {

    /**
     * @var ClientPublicKey
     */
    private $public_key;

    /**
     * @var ILogService
     */
    private $log_service;
    /**
     * @param ClientPublicKey $public_key
     * @param ILogService $log_service
     */
    public function __construct(ClientPublicKey $public_key, ILogService $log_service){
        $this->public_key  = $public_key;
        $this->log_service = $log_service;
    }

    /**
     * @param string $kid
     * @return IClientPublicKey
     */
    public function get($kid)
    {
        return $this->public_key->where('kid','=',$kid)->first();
    }

    /**
     * @param IClientPublicKey $public_key
     * @return void
     */
    public function add(IClientPublicKey $public_key)
    {
        $public_key->save();
    }

    /**
     * @param IClientPublicKey $public_key
     * @return void
     */
    public function delete(IClientPublicKey $public_key)
    {
        $public_key->delete();
    }

    /**
     * @param int $id
     * @return IClientPublicKey
     */
    public function getById($id)
    {
        return $this->public_key->find($id);
    }
}