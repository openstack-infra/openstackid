<?php

namespace oauth2\exceptions;

class InvalidRedeemAuthCodeException extends OAuth2ClientBaseException{

    public function __construct($client_id, $message = "")
    {
        $message = "Invalid Redeem AuthCode Exception: " . $message;
        parent::__construct($client_id,$message);
    }
} 