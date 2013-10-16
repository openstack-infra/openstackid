<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 10:25 AM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\responses;
use openid\OpenIdMessage;

abstract class OpenIdResponse extends OpenIdMessage {

    const HttpOkResponse      = 200;
    const HttpErrorResponse   = 400;
    const OpenId2ResponseType = "http://specs.openid.net/auth/2.0";

    protected $http_code;
    protected $content_type;

    public function __construct($http_code,$content_type){
        $this->http_code    = $http_code;
        $this->content_type = $content_type;
    }

    abstract public function getContent();

    public function getHttpCode(){
        return $this->http_code;
    }

    protected function setHttpCode($http_code){
        $this->http_code = $http_code;
    }

    public function getContentType(){
        return $this->content_type;
    }

    abstract public function getType();
}