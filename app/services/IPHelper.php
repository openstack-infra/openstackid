<?php

namespace services;
use \Request;

class IPHelper
{

    public static function getUserIp()
    {
        $ip = Request::server('HTTP_CLIENT_IP');
        if (empty($ip))
            $ip = Request::server('HTTP_X_FORWARDED_FOR');
        if (empty($ip))
            $ip = Request::server('REMOTE_ADDR');
        return $ip;
    }
} 