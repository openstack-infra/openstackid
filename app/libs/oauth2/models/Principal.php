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

namespace oauth2\models;

/**
 * Class Principal
 * @package oauth2\models
 */
final class Principal implements IPrincipal
{
    /**
     * @var int
     */
    private $user_id;
    /**
     * @var int
     */
    private $auth_time;

    /**
     * @return int
     */
    public function getAuthTime()
    {
        return $this->auth_time;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param array $state
     * @return $this
     */
    public function setState(array $state)
    {
        $this->user_id   = $state[0];
        $this->auth_time = $state[1];
        return $this;
    }
}