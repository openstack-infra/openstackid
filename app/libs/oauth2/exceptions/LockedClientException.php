<?php

namespace oauth2\exceptions;

class LockedClientException extends OAuth2ClientBaseException
{
    public function __construct($client_id, $message = "")
    {
        $message = "Locked Client Exception: " . $message;
        parent::__construct($client_id,$message);
    }
}