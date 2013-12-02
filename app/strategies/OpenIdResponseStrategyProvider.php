<?php

namespace strategies;

use Illuminate\Support\ServiceProvider;
use openid\responses\OpenIdDirectResponse;
use openid\responses\OpenIdIndirectResponse;
use openid\services\OpenIdRegistry;

class OpenIdResponseStrategyProvider extends ServiceProvider
{


    public function boot()
    {
        $this->app->singleton(OpenIdDirectResponse::OpenIdDirectResponse, 'strategies\\OpenIdDirectResponseStrategy');
        $this->app->singleton(OpenIdIndirectResponse::OpenIdIndirectResponse, 'strategies\\OpenIdIndirectResponseStrategy');

        OpenIdRegistry::getInstance()->set(OpenIdDirectResponse::OpenIdDirectResponse, $this->app->make(OpenIdDirectResponse::OpenIdDirectResponse));
        OpenIdRegistry::getInstance()->set(OpenIdIndirectResponse::OpenIdIndirectResponse, $this->app->make(OpenIdIndirectResponse::OpenIdIndirectResponse));
    }

    public function register()
    {

    }
}