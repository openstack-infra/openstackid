<?php

namespace oauth2\responses;

use oauth2\OAuth2Protocol;

class OAuth2IndirectErrorResponse extends OAuth2IndirectResponse {

    public function __construct($error, $return_to=null){
        $this[OAuth2Protocol::OAuth2Protocol_Error] = $error;
        $this->return_to  = $return_to;
    }

    public function setError($error){
        $this[OAuth2Protocol::OAuth2Protocol_Error] = $error;
    }
} 