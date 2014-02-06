<?php

namespace oauth2\exceptions;

class InvalidClientCredentials extends OAuth2ClientBaseException
{
    public function __construct($client_id, $message = "")
    {
        $message = "Invalid Client Credentials : " . $message;
        parent::__construct($client_id, $message);
    }
}