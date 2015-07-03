<?php

namespace oauth2\responses;

use oauth2\OAuth2Protocol;
use utils\http\HttpContentType;

/**
 * Class OAuth2DirectErrorResponse
 * @package oauth2\responses
 */
class OAuth2DirectErrorResponse extends OAuth2DirectResponse
{

    /**
     * @param string $error
     * @param null|string $error_description
     * @param null|string $state
     */
    public function __construct($error, $error_description = null, $state = null)
    {
        // Error Response: A server receiving an invalid request MUST send a
        // response with an HTTP status code of 400.
        parent::__construct(self::HttpErrorResponse, HttpContentType::Json);
        $this->setError($error);

        if(!empty ($error_description))
            $this->setErrorDescription($error_description);

        if(!empty($state))
            $this->setState($state);
    }

    /**
     * @param string $error
     */
    public function setError($error)
    {
        $this[OAuth2Protocol::OAuth2Protocol_Error] = $error;
        return $this;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this[OAuth2Protocol::OAuth2Protocol_State] = $state;
        return $this;
    }

    /**
     * @param string $error_description
     */
    public function setErrorDescription($error_description)
    {
        $this[OAuth2Protocol::OAuth2Protocol_ErrorDescription] = $error_description;
        return $this;
    }
} 