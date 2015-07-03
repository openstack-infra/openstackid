<?php

namespace oauth2\responses;

use oauth2\OAuth2Protocol;

/**
 * Class OAuth2IndirectFragmentErrorResponse
 * @package oauth2\responses
 */
class OAuth2IndirectFragmentErrorResponse extends OAuth2IndirectFragmentResponse
{

    /**
     * @param string $error
     * @param string $error_description
     * @param null|string $return_to
     * @param null|string $state
     */
    public function __construct($error, $error_description, $return_to = null,  $state = null)
    {
        parent::__construct();

        if(!empty($state))
            $this[OAuth2Protocol::OAuth2Protocol_State] = $state;

        if(!empty($error_description))
            $this->setErrorDescription($error_description);

        $this->setError($error);

        $this->setReturnTo($return_to);
    }

    public function setError($error)
    {
        $this[OAuth2Protocol::OAuth2Protocol_Error] = $error;
    }

    public function setErrorDescription($error_description)
    {
        $this[OAuth2Protocol::OAuth2Protocol_ErrorDescription] = $error_description;
    }

} 