<?php

namespace strategies;

use Illuminate\Support\ServiceProvider;
use oauth2\responses\OAuth2DirectResponse;
use oauth2\responses\OAuth2IndirectResponse;
use openid\responses\OpenIdDirectResponse;
use openid\responses\OpenIdIndirectResponse;
use utils\services\Registry;

class StrategyProvider extends ServiceProvider
{


    public function boot()
    {
        //direct response strategy
        $this->app->singleton(OAuth2DirectResponse::OAuth2DirectResponse, 'strategies\\DirectResponseStrategy');
        $this->app->singleton(OpenIdDirectResponse::OpenIdDirectResponse, 'strategies\\DirectResponseStrategy');
        //indirect response strategy
        $this->app->singleton(OpenIdIndirectResponse::OpenIdIndirectResponse, 'strategies\\IndirectResponseStrategy');
        $this->app->singleton(OAuth2IndirectResponse::OAuth2IndirectResponse, 'strategies\\IndirectResponseStrategy');

        $this->app->singleton('oauth2\\strategies\\IOAuth2AuthenticationStrategy', 'strategies\\OAuth2AuthenticationStrategy');

        Registry::getInstance()->set(OpenIdDirectResponse::OpenIdDirectResponse, $this->app->make(OpenIdDirectResponse::OpenIdDirectResponse));
        Registry::getInstance()->set(OAuth2DirectResponse::OAuth2DirectResponse, $this->app->make(OAuth2DirectResponse::OAuth2DirectResponse));

        Registry::getInstance()->set(OpenIdIndirectResponse::OpenIdIndirectResponse, $this->app->make(OpenIdIndirectResponse::OpenIdIndirectResponse));
        Registry::getInstance()->set(OAuth2IndirectResponse::OAuth2IndirectResponse, $this->app->make(OAuth2IndirectResponse::OAuth2IndirectResponse));
    }

    public function register()
    {

    }
}