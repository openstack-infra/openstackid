<?php

namespace oauth2\models;

abstract class Token {

    protected $value;
    protected $lifetime;
    protected $issued;
    protected $client_id;
    protected $len;
    protected $scope;
    protected $audience;
    protected $from_ip;

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

    public function getScope(){
        return $this->scope;
    }

    public function getClientId(){
       return $this->client_id;
    }

    public function getAudience(){
        return $this->audience;
    }

    public function getFromIp(){
        return $this->from_ip;
    }

    public abstract function toJSON();

    public abstract function fromJSON($json);
} 