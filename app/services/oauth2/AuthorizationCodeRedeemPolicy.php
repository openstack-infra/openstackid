<?php
namespace services\oauth2;

use DB;
use Exception;
use Log;
use oauth2\exceptions\ReplayAttackException;
use services\AbstractBlacklistSecurityPolicy;
use utils\db\ITransactionService;
use utils\services\ICacheService;
use utils\services\ILockManagerService;
use utils\services\IServerConfigurationService;

class AuthorizationCodeRedeemPolicy extends AbstractBlacklistSecurityPolicy
{

    /**
     * @param IServerConfigurationService $server_configuration_service
     * @param ILockManagerService $lock_manager_service
     * @param ICacheService $cache_service
     * @param ITransactionService $tx_service
     */
    public function __construct(
        IServerConfigurationService $server_configuration_service,
        ILockManagerService $lock_manager_service,
        ICacheService $cache_service,
        ITransactionService $tx_service
    ) {
        parent::__construct($server_configuration_service, $lock_manager_service, $cache_service, $tx_service);
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

            if ($ex instanceof ReplayAttackException) {
                $token = $ex->getToken();
                $this->counter_measure->trigger
                (
                    array
                    (
                        'auth_code' => $token
                    )
                );
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

}