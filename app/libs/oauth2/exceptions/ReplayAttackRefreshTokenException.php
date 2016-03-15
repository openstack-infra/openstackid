<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 3/15/16
 * Time: 9:26 AM
 */

namespace libs\oauth2\exceptions;

use oauth2\exceptions\ReplayAttackException;

/**
 * Class ReplayAttackRefreshTokenException
 * @package libs\oauth2\exceptions
 */
final class ReplayAttackRefreshTokenException extends ReplayAttackException
{
    /**
     * ReplayAttackAuthCodeException constructor.
     * @param null|string $refresh_token
     * @param null $description
     */
    public function __construct($refresh_token, $description = null)
    {
        parent::__construct($refresh_token, $description);
    }
}