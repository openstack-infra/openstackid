<?php

namespace oauth2;

use oauth2\requests\OAuth2Request;
use oauth2\endpoints\AuthorizationEndpoint;
use oauth2\endpoints\TokenEndpoint;

class OAuth2Protocol implements  IOAuth2Protocol{

    private $authorize_endpoint;
    private $token_endpoint;


    const OAuth2Protocol_ResponseType  = "response_type";
    const OAuth2Protocol_ClientId      = "client_id";
    const OAuth2Protocol_RedirectUri   = "redirect_uri";
    const OAuth2Protocol_Scope         = "scope";
    const OAuth2Protocol_State         = "state";

    public static $protocol_definition = array(
        self::OAuth2Protocol_ResponseType => self::OAuth2Protocol_ResponseType,
        self::OAuth2Protocol_ClientId     => self::OAuth2Protocol_ClientId,
        self::OAuth2Protocol_RedirectUri  => self::OAuth2Protocol_RedirectUri,
        self::OAuth2Protocol_Scope        => self::OAuth2Protocol_Scope,
        self::OAuth2Protocol_State        => self::OAuth2Protocol_State
    );

    public function __construct(){
        $this->authorize_endpoint = new AuthorizationEndpoint;
        $this->token_endpoint     = new TokenEndpoint;
    }

    public function authorize(OAuth2Request $request)
    {
        return $this->authorize_endpoint->handle($request);
    }

    public function token(OAuth2Request $request)
    {
        return $this->token_endpoint->handle($request);
    }
}