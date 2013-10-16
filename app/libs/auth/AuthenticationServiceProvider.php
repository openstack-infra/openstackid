<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 4:14 PM
 * To change this template use File | Settings | File Templates.
 */

namespace auth;
use Illuminate\Support\ServiceProvider;

class AuthenticationServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton('openid\\services\\IAuthService','auth\\AuthService');
    }
}