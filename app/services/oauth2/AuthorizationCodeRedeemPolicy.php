<?php

namespace services\oauth2;

use Exception;
use DB;
use Log;
use utils\services\IServerConfigurationService;
use services\AbstractBlacklistSecurityPolicy;
use utils\services\ILockManagerService;

class AuthorizationCodeRedeemPolicy extends AbstractBlacklistSecurityPolicy {

    public function __construct(IServerConfigurationService $server_configuration_service, ILockManagerService $lock_manager_service)
    {
        parent::__construct($server_configuration_service,$lock_manager_service);
    }

    /**
     * Check if current security policy is meet or not
     * @return boolean
     */
    public function check()
    {
        return true;
    }

    /**
     * Apply security policy on a exception
     * @param Exception $ex
     * @return mixed
     */
    public function apply(Exception $ex)
    {
        try {
            $exception_class = get_class($ex);
            switch ($exception_class) {
                case 'oauth2\exceptions\ReplayAttackException':
                {
                    $auth_code = $ex->getAuthCode();
                    $this->counter_measure->trigger(array('auth_code'=>$auth_code));
                }
                break;
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

}