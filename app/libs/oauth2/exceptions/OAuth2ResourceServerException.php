<?php

namespace oauth2\exceptions;

use Exception;

class OAuth2ResourceServerException  extends Exception{

    private $http_code;
    private $error;
    private $error_description;
    private $scope;

    public function __construct($http_code,$error,$error_description,$scope = null)
    {
        $this->http_code = $http_code;
        $this->error = $error;
        $this->error_description = $error_description;
        $this->scope = $scope;
        $message = "Resource Server Exception : " . sprintf('http code : %s  - error : %s - error description: %s',$http_code,$error, $error_description);
        parent::__construct($message, 0, null);
    }

    public function getError(){
        return $this->error;
    }

    public function getErrorDescription(){
        return $this->error_description;
    }

    public function getScope(){
        return $this->scope;
    }

    public function getHttpCode(){
        return $this->http_code;
    }
} 