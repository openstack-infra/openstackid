<?php

namespace oauth2\models;


use Zend\Math\Rand;

class AuthorizationCode extends Token {

    private $redirect_uri;

    public function __construct($client_id,$redirect_uri,$lifetime=3600){
        parent::__construct(Token::DefaultByteLength);
        $this->value        = Rand::getString(Token::DefaultByteLength,null,true);
        $this->redirect_uri = $redirect_uri;
        $this->client_id    = $client_id;
        $this->lifetime     = $lifetime;
    }

    public function toJSON()
    {
        $o = array(
            'redirect_uri' =>$this->redirect_uri,
            'client_id' =>$this->client_id,
        );
        return json_encode($o);
    }
}