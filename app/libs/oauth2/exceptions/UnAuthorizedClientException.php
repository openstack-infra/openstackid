<?php

namespace oauth2\exceptions;

class UnAuthorizedClientException extends OAuth2ClientBaseException
{
    public function __construct($client_id, $message = "")
    {
        $message = "UnAuthorized Client: " . $message;
        parent::__construct($client_id,$message);
    }
}