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
        $this->app->singleton('services\\IUserActionService', 'services\\UserActionService');

        Registry::getInstance()->set(ServiceCatalog::MementoService, $this->app->make(ServiceCatalog::MementoService));
        Registry::getInstance()->set(ServiceCatalog::AuthenticationStrategy, $this->app->make(ServiceCatalog::AuthenticationStrategy));
        Registry::getInstance()->set(ServiceCatalog::ServerExtensionsService, $this->app->make(ServiceCatalog::ServerExtensionsService));
        Registry::getInstance()->set(ServiceCatalog::AssociationService, $this->app->make(ServiceCatalog::AssociationService));
        Registry::getInstance()->set(ServiceCatalog::TrustedSitesService, $this->app->make(ServiceCatalog::TrustedSitesService));
        Registry::getInstance()->set(ServiceCatalog::ServerConfigurationService, $this->app->make(ServiceCatalog::ServerConfigurationService));
        Registry::getInstance()->set(ServiceCatalog::UserService, $this->app->make(ServiceCatalog::UserService));
        Registry::getInstance()->set(ServiceCatalog::NonceService, $this->app->make(ServiceCatalog::NonceService));
        Registry::getInstance()->set(ServiceCatalog::LogService, $this->app->make(ServiceCatalog::LogService));
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