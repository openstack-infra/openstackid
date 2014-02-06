<?php

namespace auth;

use Illuminate\Support\ServiceProvider;
use utils\services\Registry;
use utils\services\UtilsServiceCatalog;

class AuthenticationServiceProvider extends ServiceProvider
{


    public function boot()
    {
    }

    public function register()
    {
        $this->app->singleton(UtilsServiceCatalog::AuthenticationService, 'auth\\AuthService');
        Registry::getInstance()->set(UtilsServiceCatalog::AuthenticationService, $this->app->make(UtilsServiceCatalog::AuthenticationService));

        $this->app->singleton('auth\\IAuthenticationExtensionService', 'auth\\AuthenticationExtensionService');
        Registry::getInstance()->set('auth\\IAuthenticationExtensionService', $this->app->make('auth\\IAuthenticationExtensionService'));
    }
}