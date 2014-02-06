<?php

namespace services\oauth2;

use Illuminate\Support\ServiceProvider;
use oauth2\services\OAuth2ServiceCatalog;
use services\oauth2\ResourceServer;

/**
 * Class OAuth2ServiceProvider
 * @package services\oauth2
 */
class OAuth2ServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot(){
    }

    public function register(){

        $this->app->singleton('oauth2\\IResourceServerContext', 'services\\oauth2\\ResourceServerContext');

        $this->app->singleton(OAuth2ServiceCatalog::MementoService, 'services\\oauth2\\MementoOAuth2AuthenticationRequestService');
        $this->app->singleton(OAuth2ServiceCatalog::ClientService, 'services\\oauth2\\ClientService');
        $this->app->singleton(OAuth2ServiceCatalog::TokenService, 'services\\oauth2\\TokenService');
        $this->app->singleton(OAuth2ServiceCatalog::ScopeService, 'services\\oauth2\\ApiScopeService');
        $this->app->singleton(OAuth2ServiceCatalog::ResourceServerService, 'services\\oauth2\\ResourceServerService');
        $this->app->singleton(OAuth2ServiceCatalog::ApiService, 'services\\oauth2\\ApiService');
        $this->app->singleton(OAuth2ServiceCatalog::ApiEndpointService, 'services\\oauth2\\ApiEndpointService');
        $this->app->singleton(OAuth2ServiceCatalog::UserConsentService, 'services\\oauth2\\UserConsentService');
        $this->app->singleton(OAuth2ServiceCatalog::AllowedOriginService, 'services\\oauth2\\AllowedOriginService');
        //OAUTH2 resource server endpoints
        $this->app->singleton('oauth2\resource_server\IUserService', 'services\oauth2\resource_server\UserService');
    }

    public function provides()
    {
        return array('oauth2.services');
    }
}