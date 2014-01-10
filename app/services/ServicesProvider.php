<?php

namespace services;

use Illuminate\Support\ServiceProvider;
use openid\services\OpenIdServiceCatalog;
use utils\services\Registry;
use oauth2\services\OAuth2ServiceCatalog;
use utils\services\UtilsServiceCatalog;

class ServicesProvider extends ServiceProvider
{

    public function boot()
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
        $this->app->singleton(UtilsServiceCatalog::LogService, 'services\\LogService');
        $this->app->singleton(UtilsServiceCatalog::LockManagerService, 'services\\LockManagerService');
        $this->app->singleton(UtilsServiceCatalog::ServerConfigurationService, 'services\\ServerConfigurationService');

        $this->app->singleton("services\\DelayCounterMeasure", 'services\\DelayCounterMeasure');
        $this->app->singleton("services\\LockUserCounterMeasure", 'services\\LockUserCounterMeasure');
        $this->app->singleton("services\\oauth2\\RevokeAuthorizationCodeRelatedTokens", 'services\\oauth2\\RevokeAuthorizationCodeRelatedTokens');

        $this->app->singleton("services\\BlacklistSecurityPolicy", 'services\\BlacklistSecurityPolicy');
        $this->app->singleton("services\\LockUserSecurityPolicy", 'services\\LockUserSecurityPolicy');

        $this->app->singleton("services\\oauth2\\AuthorizationCodeRedeemPolicy", 'services\\oauth2\\AuthorizationCodeRedeemPolicy');

        $this->app->singleton('services\\IUserActionService', 'services\\UserActionService');
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
            //policies...
            $checkpoint_service = new CheckPointService($blacklist_security_policy);
            $checkpoint_service->addPolicy($lock_user_security_policy);
            $checkpoint_service->addPolicy($authorization_code_redeem_Policy);
            return $checkpoint_service;
        });

        Registry::getInstance()->set(OpenIdServiceCatalog::MementoService, $this->app->make(OpenIdServiceCatalog::MementoService));
        Registry::getInstance()->set(OpenIdServiceCatalog::AuthenticationStrategy, $this->app->make(OpenIdServiceCatalog::AuthenticationStrategy));
        Registry::getInstance()->set(OpenIdServiceCatalog::ServerExtensionsService, $this->app->make(OpenIdServiceCatalog::ServerExtensionsService));
        Registry::getInstance()->set(OpenIdServiceCatalog::AssociationService, $this->app->make(OpenIdServiceCatalog::AssociationService));
        Registry::getInstance()->set(OpenIdServiceCatalog::TrustedSitesService, $this->app->make(OpenIdServiceCatalog::TrustedSitesService));
        Registry::getInstance()->set(OpenIdServiceCatalog::ServerConfigurationService, $this->app->make(OpenIdServiceCatalog::ServerConfigurationService));
        Registry::getInstance()->set(OpenIdServiceCatalog::UserService, $this->app->make(OpenIdServiceCatalog::UserService));
        Registry::getInstance()->set(OpenIdServiceCatalog::NonceService, $this->app->make(OpenIdServiceCatalog::NonceService));

        Registry::getInstance()->set(UtilsServiceCatalog::LogService, $this->app->make(UtilsServiceCatalog::LogService));
        Registry::getInstance()->set(UtilsServiceCatalog::CheckPointService, $this->app->make(UtilsServiceCatalog::CheckPointService));
        Registry::getInstance()->set(UtilsServiceCatalog::ServerConfigurationService, $this->app->make(UtilsServiceCatalog::ServerConfigurationService));

        $this->app->singleton(OAuth2ServiceCatalog::MementoService, 'services\\oauth2\\MementoOAuth2AuthenticationRequestService');
        $this->app->singleton(OAuth2ServiceCatalog::ClientService, 'services\\oauth2\\ClientService');
        $this->app->singleton(OAuth2ServiceCatalog::TokenService, 'services\\oauth2\\TokenService');
        $this->app->singleton(OAuth2ServiceCatalog::ScopeService, 'services\\oauth2\\ApiScopeService');
        $this->app->singleton(OAuth2ServiceCatalog::ResourceServerService, 'services\\oauth2\\ResourceServerService');

        Registry::getInstance()->set(OAuth2ServiceCatalog::MementoService, $this->app->make(OAuth2ServiceCatalog::MementoService));
        Registry::getInstance()->set(OAuth2ServiceCatalog::TokenService, $this->app->make(OAuth2ServiceCatalog::TokenService));
        Registry::getInstance()->set(OAuth2ServiceCatalog::ScopeService, $this->app->make(OAuth2ServiceCatalog::ScopeService));
        Registry::getInstance()->set(OAuth2ServiceCatalog::ClientService, $this->app->make(OAuth2ServiceCatalog::ClientService));
        Registry::getInstance()->set(OAuth2ServiceCatalog::ResourceServerService, $this->app->make(OAuth2ServiceCatalog::ResourceServerService));
    }

    public function register()
    {
        $this->app['serverconfigurationservice'] = $this->app->share(function ($app) {
            return new ServerConfigurationService();
        });

        // Shortcut so developers don't need to add an Alias in app/config/app.php
        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('ServerConfigurationService', 'services\\Facades\\ServerConfigurationService');
        });
    }

}