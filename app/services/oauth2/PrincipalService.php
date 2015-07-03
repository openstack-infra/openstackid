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

namespace services\oauth2;

use oauth2\models\IPrincipal;
use oauth2\models\Principal;
use oauth2\services\IPrincipalService;
use Session;

/**
 * Class PrincipalService
 * @package services\oauth2
 */
final class PrincipalService implements IPrincipalService
{

    const UserIdParam = 'openstackid.oauth2.principal.user_id';
    const AuthTimeParam = 'openstackid.oauth2.principal.auth_time';

    /**
     * @return IPrincipal
     */
    public function get()
    {
        $principal = new Principal;

        $user_id = Session::get(self::UserIdParam);
        $auth_time = Session::get(self::AuthTimeParam);

        $principal->setState
        (
            array
            (
                $user_id,
                $auth_time
            )
        );

        return $principal;
    }

    /**
     * @param IPrincipal $principal
     * @return void
     */
    public function save(IPrincipal $principal)
    {
        $this->register($principal->getUserId(), $principal->getAuthTime());
    }

    /**
     * @param int $user_id
     * @param int $auth_time
     * @return mixed
     */
    public function register($user_id, $auth_time)
    {
        Session::put(self::UserIdParam, $user_id);
        Session::put(self::AuthTimeParam, $auth_time);
    }

    /**
     * @return $this
     */
    public function clear()
    {
        Session::remove(self::UserIdParam);
        Session::remove(self::AuthTimeParam);
    }
}