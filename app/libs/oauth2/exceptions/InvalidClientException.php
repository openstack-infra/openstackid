<?php

namespace oauth2\exceptions;

class InvalidClientException extends OAuth2ClientBaseException
{
    public function __construct($client_id, $message = "")
    {
        $message = "Invalid OAuth2 Client : " . $message;
        parent::__construct($client_id, $message);
    }
}