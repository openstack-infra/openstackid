<?php

namespace oauth2;

use Illuminate\Support\ServiceProvider;
use utils\services\Registry;

class OAuth2ServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Registry::getInstance()->set('oauth2\IOAuth2Protocol', $this->app->make('oauth2\IOAuth2Protocol'));
    }

    public function register()
    {
        $this->app->singleton('oauth2\IOAuth2Protocol', 'oauth2\OAuth2Protocol');
    }

    public function provides()
    {
        return array('oauth2');
    }
}