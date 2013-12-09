<?php

namespace oauth2\endpoints;

use oauth2\requests\OAuth2Request;
use oauth2\OAuth2Protocol;
use oauth2\grant_types\AuthorizationCodeGrantType;
use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\services\IClientService;
use oauth2\services\IMementoOAuth2AuthenticationRequestService;
use oauth2\services\ITokenService;
use utils\services\IAuthService;
use oauth2\strategies\IOAuth2AuthenticationStrategy;

/**
 * Class TokenEndpoint
 * Token Endpoint Implementation
 * http://tools.ietf.org/html/rfc6749#section-3.2
 * @package oauth2\endpoints
 */
class TokenEndpoint implements IOAuth2Endpoint {


    private $grant_types = array ();

    public function __construct(IClientService $client_service,
                                ITokenService $token_service,
                                IAuthService $auth_service,
                                IMementoOAuth2AuthenticationRequestService $memento_service,
                                IOAuth2AuthenticationStrategy $auth_strategy){
        $this->grant_types[OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode] = new AuthorizationCodeGrantType($client_service,$token_service,$auth_service,$memento_service,$auth_strategy);
    }

    public function handle(OAuth2Request $request)
    {
        foreach($this->grant_types as $key => $grant){
            if($grant->canHandle($request))
                return $grant->completeFlow($request);
        }
        throw new InvalidOAuth2Request;
    }
}