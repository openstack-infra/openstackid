<?php
namespace services;

use Illuminate\Support\ServiceProvider;
use utils\services\UtilsServiceCatalog;
use services\oauth2\ResourceServer;
use services\utils\CheckPointService;

/**
 * Class ServicesProvider
 * @package services
 */
class ServicesProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot(){


    }

    public function register(){

        $this->app->singleton('services\\IUserActionService', 'services\\UserActionService');
        $this->app->singleton("services\\DelayCounterMeasure", 'services\\DelayCounterMeasure');
        $this->app->singleton("services\\LockUserCounterMeasure", 'services\\LockUserCounterMeasure');
        $this->app->singleton("services\\oauth2\\RevokeAuthorizationCodeRelatedTokens", 'services\\oauth2\\RevokeAuthorizationCodeRelatedTokens');
        $this->app->singleton("services\\BlacklistSecurityPolicy", 'services\\BlacklistSecurityPolicy');
        $this->app->singleton("services\\LockUserSecurityPolicy", 'services\\LockUserSecurityPolicy');
        $this->app->singleton("services\\OAuth2LockClientCounterMeasure", 'services\\OAuth2LockClientCounterMeasure');
        $this->app->singleton("services\\OAuth2SecurityPolicy", 'services\\OAuth2SecurityPolicy');
        $this->app->singleton("services\\oauth2\\AuthorizationCodeRedeemPolicy", 'services\\oauth2\\AuthorizationCodeRedeemPolicy');

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

                $oauth2_lock_client_counter_measure = $this->app->make("services\\OAuth2LockClientCounterMeasure");
                $oauth2_security_policy             = $this->app->make("services\\OAuth2SecurityPolicy");
                $oauth2_security_policy->setCounterMeasure($oauth2_lock_client_counter_measure);

                $checkpoint_service = new CheckPointService($blacklist_security_policy);
                $checkpoint_service->addPolicy($lock_user_security_policy);
                $checkpoint_service->addPolicy($authorization_code_redeem_Policy);
                $checkpoint_service->addPolicy($oauth2_security_policy);
                return $checkpoint_service;
            });

    }

    public function provides()
    {
        return array('application.services');
    }
}