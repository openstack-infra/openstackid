<?php

namespace oauth2\exceptions;


class InvalidApplicationType extends OAuth2ClientBaseException
{
    public function __construct($client_id, $message = "")
    {
        $message = "Invalid Application Type: " . $message;
        parent::__construct($client_id,$message);
    }
}