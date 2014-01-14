<?php

namespace services;

use auth\User;
use Exception;
use openid\model\IOpenIdUser;
use UserAction;

class UserActionService implements IUserActionService
{

    public function addUserAction(IOpenIdUser $user, $ip, $user_action, $realm=null)
    {
        try {
            $action = new UserAction();
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