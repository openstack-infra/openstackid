<?php

namespace services;

use openid\model\IOpenIdUser;

interface IUserActionService
{

    const LoginAction = 'LOGIN';
    const LogoutAction = 'LOGOUT';
    const ConsentAction = 'CONSENT';

    public function addUserAction(IOpenIdUser $user, $ip, $action);
} 