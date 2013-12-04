<?php

namespace oauth2\models;

class AccessToken extends Token {

    private $scopes = array();

    public function addScope($scope){
        array_push($this->scopes, $scope);
    }

    public function toJSON(){
        return '{}';
    }
} 