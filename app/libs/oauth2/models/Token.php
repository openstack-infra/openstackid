<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/3/13
 * Time: 6:14 PM
 */

namespace oauth2\models;


abstract class Token {

    protected $value;
    protected $lifetime;
    protected $issued;
    protected $client_id;
    protected $len;
    const DefaultByteLength = 32;

    public function __construct($len = self::DefaultByteLength){
        $this->len    = $len;
        $this->issued =  gmdate("Y-m-d H:i:s", time());
    }

    public function getIssued(){
        return $this->issued;
    }

    public function getValue(){
        return $this->value;
    }

    public function getLifetime(){
        return $this->lifetime;
    }

    public abstract function toJSON();
} 