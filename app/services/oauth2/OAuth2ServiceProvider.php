<?php

namespace services\oauth2;

use Illuminate\Support\ServiceProvider;
use oauth2\services\AccessTokenGenerator;
use oauth2\services\AuthorizationCodeGenerator;
use oauth2\services\OAuth2ServiceCatalog;
use oauth2\services\RefreshTokenGenerator;
use oauth2\strategies\ClientAuthContextValidatorFactory;
use services\oauth2\ResourceServer;
use App;
use utils\services\UtilsServiceCatalog;
use URL;

/**
 * Class OAuth2ServiceProvider
 * @package services\oauth2
 */
class OAuth2ServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
    }

    public function register()
    {
        App::singleton('oauth2\\IResourceServerContext', 'services\\oauth2\\ResourceServerContext');
        App::singleton(OAuth2ServiceCatalog::ClientCredentialGenerator, 'services\\oauth2\\ClientCrendentialGenerator');
        App::singleton(OAuth2ServiceCatalog::ClientService, 'services\\oauth2\\ClientService');
        App::singleton(OAuth2ServiceCatalog::ClienPublicKeyService, 'services\\oauth2\\ClienPublicKeyService');
        App::singleton(OAuth2ServiceCatalog::ServerPrivateKeyService, 'services\\oauth2\\ServerPrivateKeyService');
        App::singleton(OAuth2ServiceCatalog::ScopeService, 'services\\oauth2\\ApiScopeService');
        App::singleton(OAuth2ServiceCatalog::ResourceServerService, 'services\\oauth2\\ResourceServerService');
        App::singleton(OAuth2ServiceCatalog::ApiService, 'services\\oauth2\\ApiService');
        App::singleton(OAuth2ServiceCatalog::ApiEndpointService, 'services\\oauth2\\ApiEndpointService');
        App::singleton(OAuth2ServiceCatalog::UserConsentService, 'services\\oauth2\\UserConsentService');
        App::singleton(OAuth2ServiceCatalog::OpenIDProviderConfigurationService,'services\\oauth2\\OpenIDProviderConfigurationService');
        App::singleton(OAuth2ServiceCatalog::MementoSerializerService,'services\\oauth2\\OAuth2MementoSessionSerializerService');
        App::singleton(OAuth2ServiceCatalog::SecurityContextService,'services\\oauth2\\SecurityContextService');
        App::singleton(OAuth2ServiceCatalog::PrincipalService,'services\\oauth2\\PrincipalService');
        App::singleton('oauth2\services\IClientJWKSetReader','services\oauth2\HttpIClientJWKSetReader');
        App::singleton('oauth2\services\IApiScopeGroupService','services\oauth2\ApiScopeGroupService');

        App::singleton('oauth2\\builders\\IdTokenBuilder',function() {
            return new IdTokenBuilderImpl
            (
                App::make('oauth2\repositories\IServerPrivateKeyRepository'),
                new HttpIClientJWKSetReader
            );
        });

        App::singleton(OAuth2ServiceCatalog::TokenService, function()
        {
            return new TokenService
            (
                App::make(OAuth2ServiceCatalog::ClientService),
                App::make(UtilsServiceCatalog::LockManagerService),
                App::make(UtilsServiceCatalog::ServerConfigurationService),
                App::make(UtilsServiceCatalog::CacheService),
                App::make(UtilsServiceCatalog::AuthenticationService),
                App::make(OAuth2ServiceCatalog::UserConsentService),
                new AuthorizationCodeGenerator(App::make(UtilsServiceCatalog::CacheService)),
                new AccessTokenGenerator(App::make(UtilsServiceCatalog::CacheService)),
                new RefreshTokenGenerator(App::make(UtilsServiceCatalog::CacheService)),
                App::make('oauth2\repositories\IServerPrivateKeyRepository'),
                new HttpIClientJWKSetReader,
                App::make(OAuth2ServiceCatalog::SecurityContextService),
                App::make(OAuth2ServiceCatalog::PrincipalService),
                App::make('oauth2\\builders\\IdTokenBuilder'),
                App::make(UtilsServiceCatalog::TransactionService)
            );
        });

        //OAUTH2 resource server endpoints
        App::singleton('oauth2\resource_server\IUserService', 'services\oauth2\resource_server\UserService');
    }

    public function provides()
    {
        return array('oauth2.services');
    }
}