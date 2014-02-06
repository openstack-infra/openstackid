<?php

namespace utils;

use Request;

/**
 * Class IPHelper
 * @package utils
 */
class IPHelper
{
    /**
     * returns user current ip address
     * @return string
     */
    public static function getUserIp()
    {
        $remote_address = Request::server('REMOTE_ADDR');
        return $remote_address;
    }
} 