<?php

namespace utils\services;

/**
 * Class UtilsServiceCatalog
 * @package utils\services
 */
final class UtilsServiceCatalog {

    const CheckPointService          = 'utils\\services\\ICheckPointService';
    const LogService                 = 'utils\\services\\ILogService';
    const AuthenticationService      = 'utils\\services\\IAuthService';
    const LockManagerService         = 'utils\\services\\ILockManagerService';
    const ServerConfigurationService = 'utils\\services\\IServerConfigurationService';
    const CacheService               = 'utils\\services\\ICacheService';
    const BannedIpService            = 'utils\\services\\IBannedIPService';
    const TransactionService         = 'utils\\db\\ITransactionService';
} 