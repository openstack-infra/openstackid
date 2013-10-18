<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/18/13
 * Time: 5:08 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\services;


class Registry {

    private static $instance = null;

    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new Registry();
        }

        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}

    public function set($key, $value) {
        if (isset($this->registry[$key])) {
            throw new Exception("There is already an entry for key " . $key);
        }

        $this->registry[$key] = $value;
    }

    public function get($key) {
        if (!isset($this->registry[$key])) {
            throw new Exception("There is no entry for key " . $key);
        }

        return $this->registry[$key];
    }
}