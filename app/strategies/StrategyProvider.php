<?php

namespace strategies;

use Illuminate\Support\ServiceProvider;
use oauth2\responses\OAuth2IndirectResponse;
use openid\responses\OpenIdDirectResponse;
use openid\responses\OpenIdIndirectResponse;
use utils\services\Registry;

class StrategyProvider extends ServiceProvider
{


    public function boot()
    {
        $this->app->singleton(OpenIdDirectResponse::OpenIdDirectResponse, 'strategies\\DirectResponseStrategy');
        $this->app->singleton(OpenIdIndirectResponse::OpenIdIndirectResponse, 'strategies\\IndirectResponseStrategy');
        $this->app->singleton(OAuth2IndirectResponse::OpenIdIndirectResponse, 'strategies\\IndirectResponseStrategy');
        $this->app->singleton('oauth2\\strategies\\IOAuth2AuthenticationStrategy', 'strategies\\OAuth2AuthenticationStrategy');

        Registry::getInstance()->set(OpenIdDirectResponse::OpenIdDirectResponse, $this->app->make(OpenIdDirectResponse::OpenIdDirectResponse));
        Registry::getInstance()->set(OpenIdIndirectResponse::OpenIdIndirectResponse, $this->app->make(OpenIdIndirectResponse::OpenIdIndirectResponse));
        Registry::getInstance()->set(OAuth2IndirectResponse::OpenIdIndirectResponse, $this->app->make(OAuth2IndirectResponse::OpenIdIndirectResponse));
    }

    public function register()
    {

    }
}