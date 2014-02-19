<?php
namespace services;

use Illuminate\Support\ServiceProvider;
use utils\services\UtilsServiceCatalog;
use services\oauth2\ResourceServer;
use services\utils\CheckPointService;
use App;

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

        App::singleton('services\\IUserActionService', 'services\\UserActionService');
        App::singleton("services\\DelayCounterMeasure", 'services\\DelayCounterMeasure');
        App::singleton("services\\LockUserCounterMeasure", 'services\\LockUserCounterMeasure');
        App::singleton("services\\oauth2\\RevokeAuthorizationCodeRelatedTokens", 'services\\oauth2\\RevokeAuthorizationCodeRelatedTokens');
        App::singleton("services\\BlacklistSecurityPolicy", 'services\\BlacklistSecurityPolicy');
        App::singleton("services\\LockUserSecurityPolicy", 'services\\LockUserSecurityPolicy');
        App::singleton("services\\OAuth2LockClientCounterMeasure", 'services\\OAuth2LockClientCounterMeasure');
        App::singleton("services\\OAuth2SecurityPolicy", 'services\\OAuth2SecurityPolicy');
        App::singleton("services\\oauth2\\AuthorizationCodeRedeemPolicy", 'services\\oauth2\\AuthorizationCodeRedeemPolicy');

        App::singleton(UtilsServiceCatalog::CheckPointService,
            function(){
                //set security policies
                $delay_counter_measure = App::make("services\\DelayCounterMeasure");

                $blacklist_security_policy = App::make("services\\BlacklistSecurityPolicy");
                $blacklist_security_policy->setCounterMeasure($delay_counter_measure);

                $revoke_tokens_counter_measure = App::make("services\\oauth2\\RevokeAuthorizationCodeRelatedTokens");

                $authorization_code_redeem_Policy = App::make("services\\oauth2\\AuthorizationCodeRedeemPolicy");
                $authorization_code_redeem_Policy->setCounterMeasure($revoke_tokens_counter_measure);

                $lock_user_counter_measure = App::make("services\\LockUserCounterMeasure");

                $lock_user_security_policy = App::make("services\\LockUserSecurityPolicy");
                $lock_user_security_policy->setCounterMeasure($lock_user_counter_measure);

                $oauth2_lock_client_counter_measure = App::make("services\\OAuth2LockClientCounterMeasure");
                $oauth2_security_policy             = App::make("services\\OAuth2SecurityPolicy");
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