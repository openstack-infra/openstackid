<?php namespace Services;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use Illuminate\Support\ServiceProvider;
use Utils\Services\UtilsServiceCatalog;
use Services\Utils\CheckPointService;
use Illuminate\Support\Facades\App;

/**
 * Class ServicesProvider
 * @package Services
 */
final class ServicesProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot(){
    }

    public function register(){

        App::singleton(\Services\IUserActionService::class, \Services\UserActionService::class);
        App::singleton(\Services\SecurityPolicies\DelayCounterMeasure::class,  \Services\SecurityPolicies\DelayCounterMeasure::class);
        App::singleton(\Services\SecurityPolicies\LockUserCounterMeasure::class, \Services\SecurityPolicies\LockUserCounterMeasure::class);
        App::singleton(\Services\SecurityPolicies\RevokeAuthorizationCodeRelatedTokens::class,  \Services\SecurityPolicies\RevokeAuthorizationCodeRelatedTokens::class);
        App::singleton(\Services\SecurityPolicies\BlacklistSecurityPolicy::class,  \Services\SecurityPolicies\BlacklistSecurityPolicy::class);
        App::singleton(\Services\SecurityPolicies\LockUserSecurityPolicy::class,  \Services\SecurityPolicies\LockUserSecurityPolicy::class);
        App::singleton(\Services\SecurityPolicies\OAuth2LockClientCounterMeasure::class,  \Services\SecurityPolicies\OAuth2LockClientCounterMeasure::class);
        App::singleton(\Services\SecurityPolicies\OAuth2SecurityPolicy::class, \Services\SecurityPolicies\OAuth2SecurityPolicy::class);
        App::singleton(\Services\SecurityPolicies\AuthorizationCodeRedeemPolicy::class,\Services\SecurityPolicies\AuthorizationCodeRedeemPolicy::class);

        App::singleton(UtilsServiceCatalog::CheckPointService,
            function(){
                //set security policies
                $delay_counter_measure = App::make(\Services\SecurityPolicies\DelayCounterMeasure::class);

                $blacklist_security_policy = App::make(\Services\SecurityPolicies\BlacklistSecurityPolicy::class);
                $blacklist_security_policy->setCounterMeasure($delay_counter_measure);

                $revoke_tokens_counter_measure = App::make(\Services\SecurityPolicies\RevokeAuthorizationCodeRelatedTokens::class);

                $authorization_code_redeem_Policy = App::make(\Services\SecurityPolicies\AuthorizationCodeRedeemPolicy::class);
                $authorization_code_redeem_Policy->setCounterMeasure($revoke_tokens_counter_measure);

                $lock_user_counter_measure = App::make(\Services\SecurityPolicies\LockUserCounterMeasure::class);

                $lock_user_security_policy = App::make(\Services\SecurityPolicies\LockUserSecurityPolicy::class);
                $lock_user_security_policy->setCounterMeasure($lock_user_counter_measure);

                $oauth2_lock_client_counter_measure = App::make(\Services\SecurityPolicies\OAuth2LockClientCounterMeasure::class);
                $oauth2_security_policy             = App::make(\Services\SecurityPolicies\OAuth2SecurityPolicy::class);
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
        return [
            \Services\IUserActionService::class,
            \Services\SecurityPolicies\DelayCounterMeasure::class,
            \Services\SecurityPolicies\LockUserCounterMeasure::class,
            \Services\SecurityPolicies\RevokeAuthorizationCodeRelatedTokens::class,
            \Services\SecurityPolicies\BlacklistSecurityPolicy::class,
            \Services\SecurityPolicies\LockUserSecurityPolicy::class,
            \Services\SecurityPolicies\OAuth2LockClientCounterMeasure::class,
            \Services\SecurityPolicies\OAuth2SecurityPolicy::class,
            \Services\SecurityPolicies\AuthorizationCodeRedeemPolicy::class,
            UtilsServiceCatalog::CheckPointService,
        ];
    }
}