<?php

namespace services;

use auth\User;
use Exception;
use Log;
use openid\services\OpenIdServiceCatalog;
use utils\services\ServiceLocator;
use utils\services\ISecurityPolicyCounterMeasure;

class LockUserCounterMeasure implements ISecurityPolicyCounterMeasure
{

    public function trigger(array $params = array())
    {
        try {

            if (!isset($params["user_identifier"])) return;
            $user_identifier      = $params["user_identifier"];
            $server_configuration = ServiceLocator::getInstance()->getService(OpenIdServiceCatalog::ServerConfigurationService);
            $user_service         = ServiceLocator::getInstance()->getService(OpenIdServiceCatalog::UserService);

            $user = User::where('external_id', '=', $user_identifier)->first();
            if(is_null($user))
                return;
            //apply lock policy
            if (intval($user->login_failed_attempt) < intval($server_configuration->getConfigValue("MaxFailed.Login.Attempts")))
                $user_service->updateFailedLoginAttempts($user->id);
            else {
                $user_service->lockUser($user->id);
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }
}