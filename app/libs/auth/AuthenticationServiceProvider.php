<?php

namespace auth;

use Illuminate\Support\ServiceProvider;
use utils\services\UtilsServiceCatalog;

class AuthenticationServiceProvider extends ServiceProvider
{

    public function boot()
    {
    }

    public function register()
    {
        $this->app->singleton(UtilsServiceCatalog::AuthenticationService, 'auth\\AuthService');
        $this->app->singleton('auth\\IAuthenticationExtensionService', 'auth\\AuthenticationExtensionService');
    }

    public function provides()
    {
        return array('Authentication.services');
    }
}