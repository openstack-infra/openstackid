<?php
namespace oauth2\requests;
use oauth2\OAuth2Message;

abstract class OAuth2Request  extends OAuth2Message {

    public function __construct(array $values)
    {
        parent::__construct($values);
    }

    public abstract function isValid();
} 