<?php

namespace oauth2\exceptions;


class InvalidClientType extends OAuth2ClientBaseException
{
    public function __construct($client_id, $message = "")
    {
        $message = "Invalid Client Type: " . $message;
        parent::__construct($client_id,$message);
    }
}