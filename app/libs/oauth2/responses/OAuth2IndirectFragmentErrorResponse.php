<?php

namespace oauth2\responses;

use oauth2\OAuth2Protocol;

class OAuth2IndirectFragmentErrorResponse extends OAuth2IndirectFragmentResponse {

    public function __construct($error, $return_to=null){
        parent::__construct();
        $this->setError($error);
        $this->setReturnTo($return_to);
    }

    public function setError($error){
        $this[OAuth2Protocol::OAuth2Protocol_Error] = $error;
    }

} 