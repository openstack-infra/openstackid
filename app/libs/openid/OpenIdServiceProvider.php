<?php

namespace openid;

use Illuminate\Support\ServiceProvider;

/**
 * Class OpenIdServiceProvider
 * Register dependencies with IOC container for package openid
 * @package openid
 */
class OpenIdServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind('openid\IOpenIdProtocol', 'openid\OpenIdProtocol');
    }
}