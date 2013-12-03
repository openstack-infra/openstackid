<?php

namespace oauth2\endpoints;
use oauth2\requests\OAuth2Request;
use oauth2\OAuth2Protocol;
use oauth2\grant_types\AuthorizationCodeGrantType;
use oauth2\exceptions\InvalidOAuth2Request;

/**
 * Class AuthorizationEndpoint
 * @package oauth2\endpoints
 */
class AuthorizationEndpoint implements IOAuth2Endpoint {

    private $grant_types = array ();

    public function __construct(){
        $this->grant_types[OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode] = new AuthorizationCodeGrantType;
    }

    /**
     * @param OAuth2Request $request
     * @return mixed
     * @throws \oauth2\exceptions\InvalidOAuth2Request
     * @throws \oauth2\exceptions\InvalidClientException
     * @throws \oauth2\exceptions\UriNotAllowedException
     * @throws \oauth2\exceptions\ScopeNotAllowedException
     * @throws \oauth2\exceptions\UnsupportedResponseTypeException
     * @throws \oauth2\exceptions\UnAuthorizedClientException
     */
    public function handle(OAuth2Request $request)
    {
        foreach($this->grant_types as $key => $grant){
            if($grant->canHandle($request))
                return $grant->handle($request);
        }
        throw new InvalidOAuth2Request;
    }
}