<?php

namespace auth;

use Illuminate\Support\ServiceProvider;
use openid\services\OpenIdServiceCatalog;
use utils\services\Registry;

class AuthenticationServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->app->singleton(OpenIdServiceCatalog::AuthenticationService, 'auth\\AuthService');
        Registry::getInstance()->set(OpenIdServiceCatalog::AuthenticationService, $this->app->make(OpenIdServiceCatalog::AuthenticationService));
    }

    public function register()
    {

    }
}