<?php

namespace oauth2\responses;
use oauth2\OAuth2Protocol;

/**
 * Class OAuth2AuthorizationResponse
 * http://tools.ietf.org/html/rfc6749#section-4.1.2
 * @package oauth2\responses
 */
class OAuth2AuthorizationResponse extends OAuth2IndirectResponse {

    /**
     * @param $return_url
     * @param $code
     * @param null $scope
     * @param null $state
     */
    public function __construct($return_url, $code, $scope=null, $state=null)
    {
        parent::__construct();
        $this[OAuth2Protocol::OAuth2Protocol_ResponseType_Code]  = $code;
        $this->setReturnTo($return_url);

        if(!is_null($scope))
            $this[OAuth2Protocol::OAuth2Protocol_Scope] = $scope;

        if(!is_null($state))
            $this[OAuth2Protocol::OAuth2Protocol_State] = $state;
    }

    public function getAuthCode(){
        return isset($this[OAuth2Protocol::OAuth2Protocol_ResponseType_Code])?$this[OAuth2Protocol::OAuth2Protocol_ResponseType_Code] :null;
    }

    public function getState(){
        return isset($this[OAuth2Protocol::OAuth2Protocol_State])?$this[OAuth2Protocol::OAuth2Protocol_State] :null;
    }

    public function getScope(){
        return isset($this[OAuth2Protocol::OAuth2Protocol_Scope])?$this[OAuth2Protocol::OAuth2Protocol_Scope] :null;
    }

}