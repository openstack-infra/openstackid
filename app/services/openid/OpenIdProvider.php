<?php
namespace services;

use Illuminate\Support\ServiceProvider;
use utils\services\UtilsServiceCatalog;
use utils\services\Registry;
use openid\services\OpenIdServiceCatalog;

class OpenIdProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //register on boot bc we rely on Illuminate\Redis\ServiceProvider\RedisServiceProvider
        $this->app->singleton(OpenIdServiceCatalog::MementoService, 'services\\MementoRequestService');
        $this->app->singleton(OpenIdServiceCatalog::AuthenticationStrategy, 'services\\AuthenticationStrategy');
        $this->app->singleton(OpenIdServiceCatalog::ServerExtensionsService, 'services\\ServerExtensionsService');
        $this->app->singleton(OpenIdServiceCatalog::AssociationService, 'services\\AssociationService');
        $this->app->singleton(OpenIdServiceCatalog::TrustedSitesService, 'services\\TrustedSitesService');
        $this->app->singleton(OpenIdServiceCatalog::ServerConfigurationService, 'services\\ServerConfigurationService');
        $this->app->singleton(OpenIdServiceCatalog::UserService, 'services\\UserService');
        $this->app->singleton(OpenIdServiceCatalog::NonceService, 'services\\NonceService');
    }

    public function provides()
    {
        return array('openid.services');
    }
}