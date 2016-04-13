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
use Models\OAuth2\AccessToken;
use OAuth2\Repositories\IAccessTokenRepository;
use Utils\Services\ILogService;
/**
 * Class EloquentAccessTokenRepository
 * @package Repositories
 */
final class EloquentAccessTokenRepository extends AbstractEloquentOAuth2TokenRepository implements IAccessTokenRepository
{
    /**
     * @param AccessToken $entity
     * @param ILogService $log_service
     */
    public function __construct(AccessToken $entity, ILogService $log_service){
        $this->entity      = $entity;
        $this->log_service = $log_service;
    }

    /**
     * @param string $hashed_value
     * @return AccessToken
     */
    function getByAuthCode($hashed_value)
    {
        return $this->entity->where('associated_authorization_code', '=', $hashed_value)->first();
    }

    /**
     * @param int $refresh_token_id
     * @return AccessToken[]
     */
    function getByRefreshToken($refresh_token_id)
    {
        return $this->entity->where('refresh_token_id', '=', $refresh_token_id)->get();
    }
}