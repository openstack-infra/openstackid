<?php

namespace oauth2;

use Illuminate\Support\ServiceProvider;
use App;

/**
 * Class OAuth2ServiceProvider
 * @package oauth2
 */
class OAuth2ServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        App::singleton('oauth2\IOAuth2Protocol', 'oauth2\OAuth2Protocol');
    }

    public function provides()
    {
        return array('oauth2');
    }
}