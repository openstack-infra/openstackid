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

namespace oauth2\services;

use oauth2\models\IApiScopeGroup;

/**
 * Interface IApiScopeGroupService
 * @package oauth2\services
 */
interface IApiScopeGroupService
{
    /**
     * @param $id
     * @param array $params
     * @return bool
     * @throws \oauth2\exceptions\InvalidApiScopeGroup
     */
    public function update($id, array $params);

    /**
     * @param int $id
     * @param bool $status status (active/non active)
     * @return void
     */
    public function setStatus($id, $status);

    /**
     * @param string $name
     * @param bool $active
     * @param string $scopes
     * @param string $users
     * @return IApiScopeGroup
     */
    public function register($name, $active, $scopes, $users);
}