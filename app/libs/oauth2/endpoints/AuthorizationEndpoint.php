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
 * Class AuthorizationEndpoint
 * @package oauth2\endpoints
 */
class AuthorizationEndpoint implements IOAuth2Endpoint {

    private $grant_types = array ();

    public function __construct(IClientService $client_service,
                                ITokenService $token_service,
                                IAuthService $auth_service,
                                IMementoOAuth2AuthenticationRequestService $memento_service,
                                IOAuth2AuthenticationStrategy $auth_strategy){
        $this->grant_types[OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode] = new AuthorizationCodeGrantType($client_service,$token_service,$auth_service,$memento_service,$auth_strategy);
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
     * @throws \oauth2\exceptions\AccessDeniedException
     * @throws \oauth2\exceptions\OAuth2GenericException
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