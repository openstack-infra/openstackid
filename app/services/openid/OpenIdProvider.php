<?php
namespace services\openid;

use Illuminate\Support\ServiceProvider;
use openid\services\OpenIdServiceCatalog;
use App;

class OpenIdProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //register on boot bc we rely on Illuminate\Redis\ServiceProvider\RedisServiceProvider
        App::singleton(OpenIdServiceCatalog::MementoService, 'services\\openid\\MementoRequestService');
        App::singleton(OpenIdServiceCatalog::AuthenticationStrategy, 'services\\openid\\AuthenticationStrategy');
        App::singleton(OpenIdServiceCatalog::ServerExtensionsService, 'services\\openid\\ServerExtensionsService');
        App::singleton(OpenIdServiceCatalog::AssociationService, 'services\\openid\\AssociationService');
        App::singleton(OpenIdServiceCatalog::TrustedSitesService, 'services\\openid\\TrustedSitesService');
        App::singleton(OpenIdServiceCatalog::ServerConfigurationService, 'services\\utils\\ServerConfigurationService');
        App::singleton(OpenIdServiceCatalog::UserService, 'services\\openid\\UserService');
        App::singleton(OpenIdServiceCatalog::NonceService, 'services\\openid\\NonceService');
    }

    public function provides()
    {
        return array('openid.services');
    }
}