<?php

namespace auth;

use Illuminate\Support\ServiceProvider;
use utils\services\UtilsServiceCatalog;
use App;

class AuthenticationServiceProvider extends ServiceProvider
{

    public function boot()
    {
    }

    public function register()
    {
        App::singleton(UtilsServiceCatalog::AuthenticationService, 'auth\\AuthService');
	    App::singleton('auth\\IAuthenticationExtensionService', 'auth\\AuthenticationExtensionService');
    }

    public function provides()
    {
        return array('Authentication.services');
    }
}