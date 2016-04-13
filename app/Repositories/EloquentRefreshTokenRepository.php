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

use Models\OAuth2\RefreshToken;
use OAuth2\Repositories\IRefreshTokenRepository;
use Utils\Services\ILogService;

/**
 * Class EloquentRefreshTokenRepository
 * @package Repositories
 */
final class EloquentRefreshTokenRepository extends AbstractEloquentOAuth2TokenRepository implements IRefreshTokenRepository
{
    /**
     * @param RefreshToken $entity
     * @param ILogService $log_service
     */
    public function __construct(RefreshToken $entity, ILogService $log_service){
        $this->entity      = $entity;
        $this->log_service = $log_service;
    }

}