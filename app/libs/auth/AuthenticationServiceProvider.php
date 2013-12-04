<?php

namespace auth;

use Illuminate\Support\ServiceProvider;
use openid\services\OpenIdServiceCatalog;
use utils\services\Registry;
use utils\services\UtilsServiceCatalog;

class AuthenticationServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->app->singleton(UtilsServiceCatalog::AuthenticationService, 'auth\\AuthService');
        Registry::getInstance()->set(UtilsServiceCatalog::AuthenticationService, $this->app->make(UtilsServiceCatalog::AuthenticationService));
    }

    public function register()
    {

    }
}