<?php

namespace libs\oauth2\exceptions;

use oauth2\exceptions\ReplayAttackException;

/**
 * Class ReplayAttackAuthCodeException
 * @package libs\oauth2\exceptions
 */
final class ReplayAttackAuthCodeException extends ReplayAttackException
{
    /**
     * ReplayAttackAuthCodeException constructor.
     * @param null|string $auth_code
     * @param null $description
     */
    public function __construct($auth_code, $description = null)
    {
        parent::__construct($auth_code, $description);
    }
}