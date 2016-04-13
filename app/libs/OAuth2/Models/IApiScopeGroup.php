<?php namespace OAuth2\Models;
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
use Utils\Model\IEntity;
/**
 * Interface IApiScopeGroup
 * @package OAuth2\Models
 */
interface IApiScopeGroup extends IEntity
{
    /**
     * @param IApiScope $scope
     */
    public function addScope(IApiScope $scope);

    /**
     * @param IOAuth2User $user
     */
    public function addUser(IOAuth2User $user);

    /**
     * @param IApiScope $scope
     */
    public function removeScope(IApiScope $scope);

    /**
     * @param IOAuth2User $user
     */
    public function removeUser(IOAuth2User $user);

    /**
     * @return IOAuth2User[]
     */
    public function getUsers();

    /**
     * @return IApiScope[]
     */
    public function getScopes();

}