<?php

namespace oauth2\exceptions;

class BearerTokenDisclosureAttemptException extends OAuth2ClientBaseException
{
    public function __construct($client_id,$message = "")
    {
        $message = "Bearer Token Disclosure Attempt Attack: " . $message;
        parent::__construct($client_id,$message);
    }
}