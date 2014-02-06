<?php
namespace services\openid;

use Illuminate\Support\ServiceProvider;
use utils\services\UtilsServiceCatalog;
use utils\services\ServiceLocator;
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
        $this->app->singleton(OpenIdServiceCatalog::MementoService, 'services\\openid\\MementoRequestService');
        $this->app->singleton(OpenIdServiceCatalog::AuthenticationStrategy, 'services\\openid\\AuthenticationStrategy');
        $this->app->singleton(OpenIdServiceCatalog::ServerExtensionsService, 'services\\openid\\ServerExtensionsService');
        $this->app->singleton(OpenIdServiceCatalog::AssociationService, 'services\\openid\\AssociationService');
        $this->app->singleton(OpenIdServiceCatalog::TrustedSitesService, 'services\\openid\\TrustedSitesService');
        $this->app->singleton(OpenIdServiceCatalog::ServerConfigurationService, 'services\\utils\\ServerConfigurationService');
        $this->app->singleton(OpenIdServiceCatalog::UserService, 'services\\openid\\UserService');
        $this->app->singleton(OpenIdServiceCatalog::NonceService, 'services\\openid\\NonceService');
    }

    public function provides()
    {
        return array('openid.services');
    }
}