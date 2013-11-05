<?php

namespace strategies;

use Illuminate\Support\ServiceProvider;
use openid\responses\OpenIdDirectResponse;
use openid\responses\OpenIdIndirectResponse;
use openid\services\Registry;

class OpenIdResponseStrategyProvider extends ServiceProvider
{


    public function boot()
    {
        $this->app->singleton(OpenIdDirectResponse::OpenIdDirectResponse, 'strategies\\OpenIdDirectResponseStrategy');
        $this->app->singleton(OpenIdIndirectResponse::OpenIdIndirectResponse, 'strategies\\OpenIdIndirectResponseStrategy');

        Registry::getInstance()->set(OpenIdDirectResponse::OpenIdDirectResponse, $this->app->make(OpenIdDirectResponse::OpenIdDirectResponse));
        Registry::getInstance()->set(OpenIdIndirectResponse::OpenIdIndirectResponse, $this->app->make(OpenIdIndirectResponse::OpenIdIndirectResponse));
    }

    public function register()
    {

    }
}