<?php

namespace auth;

use Zend\Crypt\Hash;

class AuthHelper
{

    private static $algorithms = array(
        "none" => "none",
        "md5" => "md5",
        "sha1" => "sha1",
        "md5_v2.4" => "md5",
        "sha1_v2.4" => "sha1",
    );

    /**
     * @param $password
     * @param $salt
     * @param string $algorithm
     * @return string
     * @throws \Exception
     */
    public static function encrypt_password($password, $salt, $algorithm = "sha1")
    {
        if (!isset(self::$algorithms[$algorithm]))
            throw new \Exception(sprintf("non supported algorithm %s", $algorithm));
        if ($algorithm != 'none')
            return Hash::compute(self::$algorithms[$algorithm], $password . $salt);
        return $password;
    }

    public static function compare($hash1, $hash2)
    {
        // Due to flawed base_convert() floating poing precision,
        // only the first 10 characters are consistently useful for comparisons.
        return (substr($hash1, 0, 10) == substr($hash2, 0, 10));
    }
}