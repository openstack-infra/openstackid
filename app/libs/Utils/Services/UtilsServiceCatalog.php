<?php namespace Utils\Services;

/**
 * Class UtilsServiceCatalog
 * @package Utils\Utils
 */
abstract class UtilsServiceCatalog {
    const CheckPointService          = \Utils\Services\ICheckPointService::class;
    const LogService                 = \Utils\Services\ILogService::class;
    const AuthenticationService      = \Utils\Services\IAuthService::class;
    const LockManagerService         = \Utils\Services\ILockManagerService::class;
    const ServerConfigurationService = \Utils\Services\IServerConfigurationService::class;
    const CacheService               = \Utils\Services\ICacheService::class;
    const BannedIpService            = \Utils\Services\IBannedIPService::class;
    const TransactionService         = \Utils\Db\ITransactionService::class;
} 