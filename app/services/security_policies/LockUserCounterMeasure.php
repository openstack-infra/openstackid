<?php

namespace services;

use auth\User;
use Exception;
use Log;
use openid\services\IUserService;
use utils\services\IServerConfigurationService;
use utils\services\ISecurityPolicyCounterMeasure;

class LockUserCounterMeasure implements ISecurityPolicyCounterMeasure
{
	private $server_configuration;
	private $user_service;

	public function __construct(IUserService $user_service, IServerConfigurationService $server_configuration){
		$this->user_service         = $user_service;
		$this->server_configuration = $server_configuration;
	}

    public function trigger(array $params = array())
    {
        try {

            if (!isset($params["user_identifier"])) return;
            $user_identifier      = $params["user_identifier"];

            $user = User::where('external_id', '=', $user_identifier)->first();
            if(is_null($user))
                return;
            //apply lock policy
            if (intval($user->login_failed_attempt) < intval($this->server_configuration->getConfigValue("MaxFailed.Login.Attempts")))
	            $this->user_service->updateFailedLoginAttempts($user->id);
            else {
	            $this->user_service->lockUser($user->id);
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }
}