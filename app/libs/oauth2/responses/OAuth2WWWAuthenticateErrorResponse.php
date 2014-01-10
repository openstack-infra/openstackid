<?php

namespace oauth2\responses;

/**
 * Class OAuth2WWWAuthenticateErrorResponse
 * http://tools.ietf.org/html/rfc6750#section-3
 * @package oauth2\responses
 */
class OAuth2WWWAuthenticateErrorResponse extends OAuth2DirectResponse {

    private $realm;
    private $error;
    private $error_description;
    private $scope;
    private $http_error;

    public function __construct($realm, $error, $error_description, $scope, $http_error){
        parent::__construct($http_error, self::DirectResponseContentType);
        $this->realm             = $realm;
        $this->error             = $error;
        $this->error_description = $error_description;
        $this->scope             = $scope;
        $this->http_error        = $http_error;
    }

    public function getWWWAuthenticateHeaderValue(){
        $value=sprintf('Bearer realm="%s"',$this->realm);
        $value=$value.sprintf(', error="%s"',$this->error);
        $value=$value.sprintf(', error_description="%s"',$this->error_description);
        if(!is_null($this->scope))
            $value=$value.sprintf(', scope="%s"',$this->scope);
        return $value;
    }


    public function getContent()
    {
        $content = array(
            'error' => $this->error,
            'error_description' => $this->error_description
        );
        if(!is_null($this->scope))
           $content['scope'] = $this->scope;

        return $content;
    }

    public function getType()
    {
        return null;
    }
}