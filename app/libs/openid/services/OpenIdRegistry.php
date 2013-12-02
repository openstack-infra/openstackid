<?php

namespace openid\services;


class OpenIdRegistry
{

    private static $instance = null;

    private function __construct(){
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new OpenIdRegistry();
        }

        return self::$instance;
    }

    public function set($key, $value)
    {
        if (!isset($this->registry[$key])) {
            $this->registry[$key] = $value;
        }
    }

    public function get($key)
    {
        if (!isset($this->registry[$key])) {
            throw new \Exception("There is no entry for key " . $key);
        }

        return $this->registry[$key];
    }

    private function __clone()
    {
    }
}