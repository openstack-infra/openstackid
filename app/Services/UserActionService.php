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

use Exception;
use Auth\User;
use OpenId\Models\IOpenIdUser;
use Models\UserAction;
use Illuminate\Support\Facades\Log;

/**
 * Class UserActionService
 * @package Services
 */
class UserActionService implements IUserActionService
{

    /**
     * @param IOpenIdUser $user
     * @param string $ip
     * @param string $user_action
     * @param null|string $realm
     * @return bool
     */
    public function addUserAction(IOpenIdUser $user, $ip, $user_action, $realm=null)
    {
        try {
            $action              = new UserAction();
            $action->from_ip     = $ip;
            $action->user_action = $user_action;
            $action->realm       = $realm;
            $user = User::find($user->getId());
            if ($user) {
                $user->actions()->save($action);
                return true;
            }
            return false;
        } catch (Exception $ex) {
            Log::error($ex);
            return false;
        }
    }
} 