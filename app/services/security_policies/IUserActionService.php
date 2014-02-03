<?php

namespace services;

use openid\model\IOpenIdUser;

interface IUserActionService
{

    const LoginAction = 'LOGIN';
    const CancelLoginAction = 'CANCEL_LOGIN';
    const LogoutAction = 'LOGOUT';
    const ConsentAction = 'CONSENT';

    public function addUserAction(IOpenIdUser $user, $ip, $action, $realm=null);
} 