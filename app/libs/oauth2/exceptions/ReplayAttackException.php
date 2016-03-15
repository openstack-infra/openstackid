<?php

namespace oauth2\exceptions;

use oauth2\OAuth2Protocol;

/**
 * Class ReplayAttackException
 * @package oauth2\exceptions
 */
class ReplayAttackException extends OAuth2BaseException
{

    /**
     * ReplayAttackException constructor.
     * @param null|string $token
     * @param null $description
     */
    public function __construct($token, $description = null)
    {
        $this->token = $token;
        parent::__construct($description);
    }
    /**
     * @var string
     */
    private $token;

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
    /**
     * @return string
     */
    public function getError()
    {
       return OAuth2Protocol::OAuth2Protocol_Error_InvalidGrant;
    }
}