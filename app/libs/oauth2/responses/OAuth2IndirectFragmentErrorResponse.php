<?php

namespace oauth2\responses;

use oauth2\OAuth2Protocol;

class OAuth2IndirectFragmentErrorResponse extends OAuth2IndirectFragmentResponse {

    public function __construct($error, $return_to = null,  $state = null){
        parent::__construct();

        if(!empty($state))
            $this[OAuth2Protocol::OAuth2Protocol_State] = $state;

        $this->setError($error);
        $this->setReturnTo($return_to);
    }

    public function setError($error){
        $this[OAuth2Protocol::OAuth2Protocol_Error] = $error;
    }

} 