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
use openid\services\Registry;
use openid\services\ServiceCatalog;

class AuthenticationServiceProvider extends ServiceProvider {

    public function boot(){
        $this->app->singleton(ServiceCatalog::AuthenticationService,'auth\\AuthService');
        Registry::getInstance()->set(ServiceCatalog::AuthenticationService,$this->app->make(ServiceCatalog::AuthenticationService));
    }

    public function register()
    {

    }
}