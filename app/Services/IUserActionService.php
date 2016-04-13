<?php namespace Services;
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

/**
 * Interface IUserActionService
 * @package Services
 */
interface IUserActionService
{

    const LoginAction       = 'LOGIN';
    const CancelLoginAction = 'CANCEL_LOGIN';
    const LogoutAction      = 'LOGOUT';
    const ConsentAction     = 'CONSENT';

    /**
     * @param int $user_id
     * @param string $ip
     * @param string $action
     * @param null|string $realm
     * @return bool
     */
    public function addUserAction($user_id, $ip, $action, $realm = null);
} 