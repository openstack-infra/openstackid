<?php

namespace openid;

use Illuminate\Support\ServiceProvider;
use openid\extensions\OpenIdAuthenticationExtension;
use openid\services\OpenIdServiceCatalog;
use utils\services\UtilsServiceCatalog;

/**
 * Class OpenIdServiceProvider
 * Register dependencies with IOC container for package openid
 * @package openid
 */
class OpenIdServiceProvider extends ServiceProvider
{


    public function boot()
    {

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('openid\IOpenIdProtocol', 'openid\OpenIdProtocol');

        $auth_extension_service = $this->app->make('auth\\IAuthenticationExtensionService');

        if(!is_null($auth_extension_service)){
            $memento_service              = $this->app->make(OpenIdServiceCatalog::MementoService);
            $server_configuration_service = $this->app->make(UtilsServiceCatalog::ServerConfigurationService);
            $auth_extension_service->addExtension(new OpenIdAuthenticationExtension($memento_service,$server_configuration_service));
        }
    }

    public function provides()
    {
        return array('openid');
    }
}