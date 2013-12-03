<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/3/13
 * Time: 10:25 AM
 */

namespace oauth2;

use Illuminate\Support\ServiceProvider;

class OAuth2ServiceProvider  extends ServiceProvider
{

    public function register()
    {
        $this->app->bind('oauth2\IOAuth2Protocol', 'oauth2\OAuth2Protocol');
    }
}