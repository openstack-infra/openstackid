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

        $this->app->singleton('auth\\IAuthenticationExtensionService', 'auth\\AuthenticationExtensionService');
        Registry::getInstance()->set('auth\\IAuthenticationExtensionService', $this->app->make('auth\\IAuthenticationExtensionService'));
    }

    public function register()
    {

    }
}