<?php

namespace services;

use Illuminate\Support\ServiceProvider;
use openid\services\Registry;
use openid\services\ServiceCatalog;

class ServicesProvider extends ServiceProvider
{


    public function boot()
    {

        //register on boot bc we rely on Illuminate\Redis\ServiceProvider\RedisServiceProvider

        $this->app->singleton(ServiceCatalog::MementoService, 'services\\MementoRequestService');
        $this->app->singleton(ServiceCatalog::AuthenticationStrategy, 'services\\AuthenticationStrategy');
        $this->app->singleton(ServiceCatalog::ServerExtensionsService, 'services\\ServerExtensionsService');
        $this->app->singleton(ServiceCatalog::AssociationService, 'services\\AssociationService');
        $this->app->singleton(ServiceCatalog::TrustedSitesService, 'services\\TrustedSitesService');
        $this->app->singleton(ServiceCatalog::ServerConfigurationService, 'services\\ServerConfigurationService');
        $this->app->singleton(ServiceCatalog::UserService, 'services\\UserService');
        $this->app->singleton(ServiceCatalog::NonceService, 'services\\NonceService');
        $this->app->singleton(ServiceCatalog::LogService, 'services\\LogService');
        $this->app->singleton("services\\DelayCounterMeasure", 'services\\DelayCounterMeasure');
        $this->app->singleton("services\\LockUserCounterMeasure", 'services\\LockUserCounterMeasure');
        $this->app->singleton("services\\BlacklistSecurityPolicy", 'services\\BlacklistSecurityPolicy');
        $this->app->singleton("services\\LockUserSecurityPolicy", 'services\\LockUserSecurityPolicy');

        $this->app->singleton('services\\IUserActionService', 'services\\UserActionService');
        $this->app->singleton(ServiceCatalog::CheckPointService,
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

        Registry::getInstance()->set(ServiceCatalog::MementoService, $this->app->make(ServiceCatalog::MementoService));
        Registry::getInstance()->set(ServiceCatalog::AuthenticationStrategy, $this->app->make(ServiceCatalog::AuthenticationStrategy));
        Registry::getInstance()->set(ServiceCatalog::ServerExtensionsService, $this->app->make(ServiceCatalog::ServerExtensionsService));
        Registry::getInstance()->set(ServiceCatalog::AssociationService, $this->app->make(ServiceCatalog::AssociationService));
        Registry::getInstance()->set(ServiceCatalog::TrustedSitesService, $this->app->make(ServiceCatalog::TrustedSitesService));
        Registry::getInstance()->set(ServiceCatalog::ServerConfigurationService, $this->app->make(ServiceCatalog::ServerConfigurationService));
        Registry::getInstance()->set(ServiceCatalog::UserService, $this->app->make(ServiceCatalog::UserService));
        Registry::getInstance()->set(ServiceCatalog::NonceService, $this->app->make(ServiceCatalog::NonceService));
        Registry::getInstance()->set(ServiceCatalog::LogService, $this->app->make(ServiceCatalog::LogService));
        Registry::getInstance()->set(ServiceCatalog::CheckPointService, $this->app->make(ServiceCatalog::CheckPointService));
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