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

use oauth2\models\IPrincipal;

/**
 * Interface IPrincipalService
 * @package oauth2\services
 */
interface IPrincipalService
{
    /**
     * @return IPrincipal
     */
    public function get();

    /**
     * @param IPrincipal $principal
     * @return void
     */
    public function save(IPrincipal $principal);

    /**
     * @param int $user_id
     * @param int $auth_time
     * @param string $ops
     * @return mixed
     */
    public function register($user_id, $auth_time);

    /**
     * @return $this
     */
    public function clear();
}