<?php

namespace oauth2\exceptions;

use Exception;

class OAuth2ClientBaseException  extends Exception
{
    protected $client_id;

    public function __construct($client_id, $message = "")
    {
        $this->client_id = $client_id;
        $message = "OAuth2 Client Base Exception : " . $message;
        parent::__construct($message, 0, null);
    }

    public function getClientId(){
        return $this->client_id;
    }

}