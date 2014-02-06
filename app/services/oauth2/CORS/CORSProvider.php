<?php

namespace services\oauth2\CORS;

use Illuminate\Support\ServiceProvider;

class CORSProvider extends ServiceProvider {

    protected $defer = false;
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('CORSMiddleware', 'services\oauth2\CORS\CORSMiddleware');
    }

    public function boot(){

    }

    public function provides()
    {
        return array('oauth2.cors');
    }
}