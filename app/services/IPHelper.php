<?php

namespace services;
use \Request;

class IPHelper
{

    public static function getUserIp()
    {
        $remote_address       = Request::server('REMOTE_ADDR');
        return $remote_address;
    }
} 