<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 11/13/13
 * Time: 2:15 PM
 */

namespace services;

use Log;
use openid\services\ISecurityPolicyCounterMeasure;
use openid\services\Registry;
use openid\services\ServiceCatalog;
use auth\OpenIdUser;
use Exception;

class LockUserCounterMeasure implements ISecurityPolicyCounterMeasure
{

    public function trigger(array $params)
    {
        try {
            if (!isset($params["user_identifier"])) return;
            $user_identifier = $params["user_identifier"];
            $server_configuration = Registry::getInstance()->get(ServiceCatalog::ServerConfigurationService);
            $user_service = Registry::getInstance()->get(ServiceCatalog::UserService);

            $user = OpenIdUser::where('external_id', '=', $user_identifier)->first();
            //apply lock policy
            if ($user->login_failed_attempt < $server_configuration->getConfigValue("MaxFailed.Login.Attempts"))
                $user_service->updateFailedLoginAttempts($user->id);
            else {
                $user_service->lockUser($user->id);
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }
}