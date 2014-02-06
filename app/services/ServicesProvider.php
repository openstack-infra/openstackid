<?php

namespace services;

use Illuminate\Support\ServiceProvider;
use openid\services\OpenIdServiceCatalog;
use utils\services\Registry;
use oauth2\services\OAuth2ServiceCatalog;
use utils\services\UtilsServiceCatalog;
use services\oauth2\ResourceServer;
use Illuminate\Foundation\AliasLoader;

/**
 * Class ServicesProvider
 * @package services
 */
class ServicesProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot(){


    }

    public function register(){

      // Shortcut so developers don't need to add an Alias in app/config/app.php
        $this->app->booting(function () {
            $loader = AliasLoader::getInstance();
            $loader->alias('ServerConfigurationService', 'services\\Facades\\ServerConfigurationService');
        });

        $this->app->singleton('services\\IUserActionService', 'services\\UserActionService');
        $this->app->singleton('oauth2\\IResourceServerContext', 'services\\oauth2\\ResourceServerContext');
        $this->app->singleton("services\\DelayCounterMeasure", 'services\\DelayCounterMeasure');
        $this->app->singleton("services\\LockUserCounterMeasure", 'services\\LockUserCounterMeasure');
        $this->app->singleton("services\\oauth2\\RevokeAuthorizationCodeRelatedTokens", 'services\\oauth2\\RevokeAuthorizationCodeRelatedTokens');
        $this->app->singleton("services\\BlacklistSecurityPolicy", 'services\\BlacklistSecurityPolicy');
        $this->app->singleton("services\\LockUserSecurityPolicy", 'services\\LockUserSecurityPolicy');
        $this->app->singleton("services\\OAuth2LockClientCounterMeasure", 'services\\OAuth2LockClientCounterMeasure');
        $this->app->singleton("services\\OAuth2SecurityPolicy", 'services\\OAuth2SecurityPolicy');
        $this->app->singleton("services\\oauth2\\AuthorizationCodeRedeemPolicy", 'services\\oauth2\\AuthorizationCodeRedeemPolicy');

        $this->app->singleton(UtilsServiceCatalog::CheckPointService,
            function(){
                //set security policies
                $delay_counter_measure = $this->app->make("services\\DelayCounterMeasure");

                $blacklist_security_policy = $this->app->make("services\\BlacklistSecurityPolicy");
                $blacklist_security_policy->setCounterMeasure($delay_counter_measure);

                $revoke_tokens_counter_measure = $this->app->make("services\\oauth2\\RevokeAuthorizationCodeRelatedTokens");

                $authorization_code_redeem_Policy = $this->app->make("services\\oauth2\\AuthorizationCodeRedeemPolicy");
                $authorization_code_redeem_Policy->setCounterMeasure($revoke_tokens_counter_measure);

                $lock_user_counter_measure = $this->app->make("services\\LockUserCounterMeasure");

                $lock_user_security_policy = $this->app->make("services\\LockUserSecurityPolicy");
                $lock_user_security_policy->setCounterMeasure($lock_user_counter_measure);

                $oauth2_lock_client_counter_measure = $this->app->make("services\\OAuth2LockClientCounterMeasure");
                $oauth2_security_policy             = $this->app->make("services\\OAuth2SecurityPolicy");
                $oauth2_security_policy->setCounterMeasure($oauth2_lock_client_counter_measure);

                $checkpoint_service = new CheckPointService($blacklist_security_policy);
                $checkpoint_service->addPolicy($lock_user_security_policy);
                $checkpoint_service->addPolicy($authorization_code_redeem_Policy);
                $checkpoint_service->addPolicy($oauth2_security_policy);
                return $checkpoint_service;
            });

        Registry::getInstance()->set(UtilsServiceCatalog::CheckPointService, $this->app->make(UtilsServiceCatalog::CheckPointService));

        Registry::getInstance()->set(OpenIdServiceCatalog::MementoService, $this->app->make(OpenIdServiceCatalog::MementoService));
        Registry::getInstance()->set(OpenIdServiceCatalog::AuthenticationStrategy, $this->app->make(OpenIdServiceCatalog::AuthenticationStrategy));
        Registry::getInstance()->set(OpenIdServiceCatalog::ServerExtensionsService, $this->app->make(OpenIdServiceCatalog::ServerExtensionsService));
        Registry::getInstance()->set(OpenIdServiceCatalog::AssociationService, $this->app->make(OpenIdServiceCatalog::AssociationService));
        Registry::getInstance()->set(OpenIdServiceCatalog::TrustedSitesService, $this->app->make(OpenIdServiceCatalog::TrustedSitesService));
        Registry::getInstance()->set(OpenIdServiceCatalog::ServerConfigurationService, $this->app->make(OpenIdServiceCatalog::ServerConfigurationService));
        Registry::getInstance()->set(OpenIdServiceCatalog::UserService, $this->app->make(OpenIdServiceCatalog::UserService));
        Registry::getInstance()->set(OpenIdServiceCatalog::NonceService, $this->app->make(OpenIdServiceCatalog::NonceService));


        Registry::getInstance()->set(UtilsServiceCatalog::CheckPointService, $this->app->make(UtilsServiceCatalog::CheckPointService));

        $this->app->singleton(OAuth2ServiceCatalog::MementoService, 'services\\oauth2\\MementoOAuth2AuthenticationRequestService');
        $this->app->singleton(OAuth2ServiceCatalog::ClientService, 'services\\oauth2\\ClientService');
        $this->app->singleton(OAuth2ServiceCatalog::TokenService, 'services\\oauth2\\TokenService');
        $this->app->singleton(OAuth2ServiceCatalog::ScopeService, 'services\\oauth2\\ApiScopeService');
        $this->app->singleton(OAuth2ServiceCatalog::ResourceServerService, 'services\\oauth2\\ResourceServerService');
        $this->app->singleton(OAuth2ServiceCatalog::ApiService, 'services\\oauth2\\ApiService');
        $this->app->singleton(OAuth2ServiceCatalog::ApiEndpointService, 'services\\oauth2\\ApiEndpointService');
        $this->app->singleton(OAuth2ServiceCatalog::UserConsentService, 'services\\oauth2\\UserConsentService');
        $this->app->singleton(OAuth2ServiceCatalog::AllowedOriginService, 'services\\oauth2\\AllowedOriginService');

        Registry::getInstance()->set(OAuth2ServiceCatalog::MementoService, $this->app->make(OAuth2ServiceCatalog::MementoService));
        Registry::getInstance()->set(OAuth2ServiceCatalog::TokenService, $this->app->make(OAuth2ServiceCatalog::TokenService));
        Registry::getInstance()->set(OAuth2ServiceCatalog::ScopeService, $this->app->make(OAuth2ServiceCatalog::ScopeService));
        Registry::getInstance()->set(OAuth2ServiceCatalog::ClientService, $this->app->make(OAuth2ServiceCatalog::ClientService));
        Registry::getInstance()->set(OAuth2ServiceCatalog::ResourceServerService, $this->app->make(OAuth2ServiceCatalog::ResourceServerService));
        Registry::getInstance()->set(OAuth2ServiceCatalog::ApiService, $this->app->make(OAuth2ServiceCatalog::ApiService));
        Registry::getInstance()->set(OAuth2ServiceCatalog::ApiEndpointService, $this->app->make(OAuth2ServiceCatalog::ApiEndpointService));



        //OAUTH2 resource server endpoints
        $this->app->singleton('oauth2\resource_server\IUserService', 'services\oauth2\resource_server\UserService');
    }


    public function provides()
    {
        return array('application.services');
    }

}