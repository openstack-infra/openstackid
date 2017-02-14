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

use Models\OAuth2\ApiScope;
use OAuth2\Models\IApiScope;
use OAuth2\Repositories\IApiScopeRepository;

/**
 * Class EloquentApiScopeRepository
 * @package Repositories
 */
final class EloquentApiScopeRepository extends AbstractEloquentEntityRepository implements IApiScopeRepository
{
    /**
     * @param ApiScope $scope
     */
    public function __construct(ApiScope $scope)
    {
        $this->entity = $scope;
    }

    /**
     * @param array $scopes_names
     * @return IApiScope[]
     */
    public function getByName(array $scopes_names)
    {
        return $this->entity->where('active', '=', true)->whereIn('name', $scopes_names)->get();
    }

    /**
     * @return IApiScope[]
     */
    public function getDefaults()
    {
        return $this->entity->where('default', '=', true)->where('active', '=', true)->get();
    }

    /**
     * @return IApiScope[]
     */
    public function getActives(){
        return $this->entity
            ->with('api')
            ->with('api.resource_server')
            ->where('active', '=', true)
            ->orderBy('api_id')->get();
    }

    /**
     * @return IApiScope[]
     */
    public function getAssignableByGroups(){
        return $this->entity
            ->with('api')
            ->with('api.resource_server')
            ->where('active', '=', true)
            ->where('assigned_by_groups', '=', true)
            ->orderBy('api_id')->get();
    }

    /**
     * @param string $scope_name
     * @return IApiScope
     */
    public function getFirstByName($scope_name)
    {
        return $this->entity->where('active', '=', true)->where('name', $scope_name)->first();
    }
}