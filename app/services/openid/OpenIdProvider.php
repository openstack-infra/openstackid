<?php
namespace services\openid;

use Illuminate\Support\ServiceProvider;
use openid\services\NonceUniqueIdentifierGenerator;
use openid\services\OpenIdServiceCatalog;
use App;
use utils\services\UtilsServiceCatalog;

class OpenIdProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //register on boot bc we rely on Illuminate\Redis\ServiceProvider\RedisServiceProvider
        App::singleton(OpenIdServiceCatalog::MementoSerializerService, 'services\\openid\\OpenIdMementoSessionSerializerService');
        App::singleton(OpenIdServiceCatalog::AuthenticationStrategy, 'services\\openid\\AuthenticationStrategy');
        App::singleton(OpenIdServiceCatalog::ServerExtensionsService, 'services\\openid\\ServerExtensionsService');
        App::singleton(OpenIdServiceCatalog::AssociationService, 'services\\openid\\AssociationService');
        App::singleton(OpenIdServiceCatalog::TrustedSitesService, 'services\\openid\\TrustedSitesService');
        App::singleton(OpenIdServiceCatalog::ServerConfigurationService, 'services\\utils\\ServerConfigurationService');
        App::singleton(OpenIdServiceCatalog::UserService, 'services\\openid\\UserService');

        App::singleton(OpenIdServiceCatalog::NonceService, function(){
            return new NonceService(
                App::make(UtilsServiceCatalog::LockManagerService),
                App::make(UtilsServiceCatalog::CacheService),
                App::make(UtilsServiceCatalog::ServerConfigurationService),
                new NonceUniqueIdentifierGenerator(App::make(UtilsServiceCatalog::CacheService))
            );
        });

    }

    public function provides()
    {
        return array('openid.services');
    }
}