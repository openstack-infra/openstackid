<?php

namespace services;

use Illuminate\Support\ServiceProvider;
use openid\services\OpenIdRegistry;
use openid\services\OpenIdServiceCatalog;

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
        $this->app->singleton(OpenIdServiceCatalog::LogService, 'services\\LogService');

        $this->app->singleton("services\\DelayCounterMeasure", 'services\\DelayCounterMeasure');
        $this->app->singleton("services\\LockUserCounterMeasure", 'services\\LockUserCounterMeasure');
        $this->app->singleton("services\\BlacklistSecurityPolicy", 'services\\BlacklistSecurityPolicy');
        $this->app->singleton("services\\LockUserSecurityPolicy", 'services\\LockUserSecurityPolicy');

        $this->app->singleton('services\\IUserActionService', 'services\\UserActionService');
        $this->app->singleton(OpenIdServiceCatalog::CheckPointService,
        function(){
            //set security policies
            $delay_counter_measure = $this->app->make("services\\DelayCounterMeasure");

            $blacklist_security_policy = $this->app->make("services\\BlacklistSecurityPolicy");
            $blacklist_security_policy->setCounterMeasure($delay_counter_measure);

            $lock_user_counter_measure = $this->app->make("services\\LockUserCounterMeasure");

            $lock_user_security_policy = $this->app->make("services\\LockUserSecurityPolicy");
            $lock_user_security_policy->setCounterMeasure($lock_user_counter_measure);

            $checkpoint_service = new CheckPointService($blacklist_security_policy);
            $checkpoint_service->addPolicy($lock_user_security_policy);
            return $checkpoint_service;
        });

        OpenIdRegistry::getInstance()->set(OpenIdServiceCatalog::MementoService, $this->app->make(OpenIdServiceCatalog::MementoService));
        OpenIdRegistry::getInstance()->set(OpenIdServiceCatalog::AuthenticationStrategy, $this->app->make(OpenIdServiceCatalog::AuthenticationStrategy));
        OpenIdRegistry::getInstance()->set(OpenIdServiceCatalog::ServerExtensionsService, $this->app->make(OpenIdServiceCatalog::ServerExtensionsService));
        OpenIdRegistry::getInstance()->set(OpenIdServiceCatalog::AssociationService, $this->app->make(OpenIdServiceCatalog::AssociationService));
        OpenIdRegistry::getInstance()->set(OpenIdServiceCatalog::TrustedSitesService, $this->app->make(OpenIdServiceCatalog::TrustedSitesService));
        OpenIdRegistry::getInstance()->set(OpenIdServiceCatalog::ServerConfigurationService, $this->app->make(OpenIdServiceCatalog::ServerConfigurationService));
        OpenIdRegistry::getInstance()->set(OpenIdServiceCatalog::UserService, $this->app->make(OpenIdServiceCatalog::UserService));
        OpenIdRegistry::getInstance()->set(OpenIdServiceCatalog::NonceService, $this->app->make(OpenIdServiceCatalog::NonceService));
        OpenIdRegistry::getInstance()->set(OpenIdServiceCatalog::LogService, $this->app->make(OpenIdServiceCatalog::LogService));
        OpenIdRegistry::getInstance()->set(OpenIdServiceCatalog::CheckPointService, $this->app->make(OpenIdServiceCatalog::CheckPointService));
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