<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 4:30 PM
 * To change this template use File | Settings | File Templates.
 */

namespace services;
use Illuminate\Support\ServiceProvider;
use openid\services\Registry;
use Illuminate\Redis\Database;

class ServicesProvider extends ServiceProvider {

    public function register()
    {

        $this->app['redis'] = $this->app->share(function($app)
        {
            return new Database($app['config']['database.redis']);
        });

        $this->app->singleton('openid\\services\\IMementoOpenIdRequestService','services\\MementoRequestService');
        $this->app->singleton('openid\\handlers\\IOpenIdAuthenticationStrategy','services\\AuthenticationStrategy');
        $this->app->singleton('openid\\services\\IServerExtensionsService','services\\ServerExtensionsService');
        $this->app->singleton('openid\\services\\IAssociationService','services\\AssociationService');
        $this->app->singleton('openid\\services\\ITrustedSitesService','services\\TrustedSitesService');
        $this->app->singleton('openid\\services\\IServerConfigurationService','services\\ServerConfigurationService');
        $this->app->singleton('openid\\services\\IUserService','services\\UserService');
        $this->app->singleton('openid\\services\\INonceService','services\\NonceService');

        Registry::getInstance()->set("openid\\services\\IMementoOpenIdRequestService",\App::make("openid\\services\\IMementoOpenIdRequestService"));
        Registry::getInstance()->set("openid\\handlers\\IOpenIdAuthenticationStrategy",\App::make("openid\\handlers\\IOpenIdAuthenticationStrategy"));
        Registry::getInstance()->set("openid\\services\\IServerExtensionsService",\App::make("openid\\services\\IMementoOpenIdRequestService"));
        Registry::getInstance()->set("openid\\services\\IAssociationService",\App::make("openid\\services\\IAssociationService"));
        Registry::getInstance()->set("openid\\services\\ITrustedSitesService",\App::make("openid\\services\\ITrustedSitesService"));
        Registry::getInstance()->set("openid\\services\\IServerConfigurationService",\App::make("openid\\services\\IServerConfigurationService"));
        Registry::getInstance()->set("openid\\services\\IUserService",\App::make("openid\\services\\IUserService"));
        Registry::getInstance()->set("openid\\services\\INonceService",\App::make("openid\\services\\INonceService"));
    }

    public function provides()
    {
        return array('redis');
    }
}