<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 4:30 PM
 * To change this template use File | Settings | File Templates.
 */

namespace services;
use Illuminate\Support\ServiceProvider;

class ServicesProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton('openid\\services\\IMementoOpenIdRequestService','services\\MementoRequestService');
        $this->app->singleton('openid\\handlers\\IOpenIdAuthenticationStrategy','services\\AuthenticationStrategy');
    }
}