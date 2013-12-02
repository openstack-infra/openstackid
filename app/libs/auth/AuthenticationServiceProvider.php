<?php

namespace auth;

use Illuminate\Support\ServiceProvider;
use openid\services\OpenIdRegistry;
use openid\services\OpenIdServiceCatalog;

class AuthenticationServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->app->singleton(OpenIdServiceCatalog::AuthenticationService, 'auth\\AuthService');
        OpenIdRegistry::getInstance()->set(OpenIdServiceCatalog::AuthenticationService, $this->app->make(OpenIdServiceCatalog::AuthenticationService));
    }

    public function register()
    {

    }
}