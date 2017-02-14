<?php namespace OAuth2\Repositories;
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
use OAuth2\Models\IApiScope;
use Utils\Db\IBaseRepository;
/**
 * Interface IApiScopeRepository
 * @package OAuth2\Repositories
 */
interface IApiScopeRepository extends IBaseRepository
{
    /**
     * @param array $scopes_names
     * @return IApiScope[]
     */
    public function getByName(array $scopes_names);

    /**
     * @param string $scope_name
     * @return IApiScope
     */
    public function getFirstByName($scope_name);

    /**
     * @return IApiScope[]
     */
    public function getDefaults();

    /**
     * @return IApiScope[]
     */
    public function getActives();

    /**
     * @return IApiScope[]
     */
    public function getAssignableByGroups();
}