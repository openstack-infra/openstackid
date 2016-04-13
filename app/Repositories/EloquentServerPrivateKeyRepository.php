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
use Models\OAuth2\ServerPrivateKey;
use OAuth2\Repositories\IServerPrivateKeyRepository;
use Utils\Services\ILogService;
/**
 * Class EloquentServerPrivateKeyRepository
 * @package repositories
 */
final class EloquentServerPrivateKeyRepository
    extends EloquentAsymmetricKeyRepository
    implements IServerPrivateKeyRepository
{
    public function __construct(ServerPrivateKey $private_key, ILogService $log_service)
    {
        $this->entity      = $private_key;
        $this->log_service = $log_service;
    }
}