<?php

namespace oauth2;

use Illuminate\Support\ServiceProvider;

class OAuth2ServiceProvider extends ServiceProvider
{
    public function boot()
    {
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