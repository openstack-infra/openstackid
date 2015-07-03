<?php

namespace oauth2\responses;

use oauth2\OAuth2Protocol;

/**
 * Class OAuth2IndirectErrorResponse
 * @package oauth2\responses
 */
class OAuth2IndirectErrorResponse extends OAuth2IndirectResponse
{

    /**
     * @param $error
     * @param null $return_to
     * @param null $state
     */
    public function __construct($error, $return_to = null, $state = null)
    {
        $this[OAuth2Protocol::OAuth2Protocol_Error] = $error;
        if(!empty($state))
            $this[OAuth2Protocol::OAuth2Protocol_State] = $state;
        $this->return_to  = $return_to;
    }

    /**
     * @param $error
     */
    public function setError($error)
    {
        $this[OAuth2Protocol::OAuth2Protocol_Error] = $error;
    }

    /**
     * @param $state
     */
    public function setState($state)
    {
        $this[OAuth2Protocol::OAuth2Protocol_State] = $state;
    }
} 