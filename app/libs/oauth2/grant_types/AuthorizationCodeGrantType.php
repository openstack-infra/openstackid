<?php

namespace oauth2\grant_types;

use oauth2\requests\OAuth2Request;
use oauth2\OAuth2Protocol;
use oauth2\services\IClientService;
use oauth2\services\IMementoOAuth2AuthenticationRequestService;
use oauth2\services\ITokenService;
use ReflectionClass;
use oauth2\responses\OAuth2AuthorizationResponse;
use oauth2\exceptions\InvalidClientException;
use oauth2\exceptions\UriNotAllowedException;
use oauth2\exceptions\ScopeNotAllowedException;
use oauth2\exceptions\UnsupportedResponseTypeException;
use oauth2\exceptions\UnAuthorizedClientException;
use oauth2\exceptions\OAuth2GenericException;
use utils\services\IAuthService;
use oauth2\strategies\IOAuth2AuthenticationStrategy;
use oauth2\exceptions\AccessDeniedException;
use oauth2\models\IClient;


class AuthorizationCodeGrantType implements IGrantType {

    private $client_service;
    private $token_service;
    private $auth_service;
    private $auth_strategy;
    private $memento_service;

    public function __construct(IClientService $client_service, ITokenService $token_service, IAuthService $auth_service, IMementoOAuth2AuthenticationRequestService $memento_service, IOAuth2AuthenticationStrategy $auth_strategy){
        $this->client_service  = $client_service;
        $this->token_service   = $token_service;
        $this->auth_service    = $auth_service;
        $this->memento_service = $memento_service;
        $this->auth_strategy   = $auth_strategy;
    }

    public function canHandle(OAuth2Request $request)
    {
        $reflector = new ReflectionClass($request);
        return $reflector->getName()=='oauth2\requests\OAuth2AuthorizationRequest' && $request->isValid();
    }

    /**
     * @param OAuth2Request $request
     * @return OAuth2AuthorizationResponse
     * @throws \oauth2\exceptions\ScopeNotAllowedException
     * @throws \oauth2\exceptions\InvalidClientException
     * @throws \oauth2\exceptions\UnsupportedResponseTypeException
     * @throws \oauth2\exceptions\UriNotAllowedException
     * @throws \oauth2\exceptions\UnAuthorizedClientException
     * @throws \oauth2\exceptions\AccessDeniedException
     * @throws \oauth2\exceptions\OAuth2GenericException
     */
    public function handle(OAuth2Request $request)
    {
        $client_id     = $request->getClientId();

        $response_type = $request->getResponseType();

        if($response_type !== OAuth2Protocol::OAuth2Protocol_ResponseType_Code)
            throw new UnsupportedResponseTypeException(sprintf("response_type %s",$response_type));

        $client   = $this->client_service->getClientById($client_id);
        if(is_null($client))
            throw new InvalidClientException(sprintf("client_id %s",$client_id));

        if($client->getClientType()!== IClient::ClientType_Confidential)
            throw new UnAuthorizedClientException();
        //check redirect uri
        $redirect_uri  = $request->getRedirectUri();
        if(!$client->isUriAllowed($redirect_uri))
            throw new UriNotAllowedException(sprintf("redirect_to %s",$redirect_uri));

        //check requested scope
        $scope         = $request->getScope();
        if(!$client->isScopeAllowed($scope))
            throw new ScopeNotAllowedException(sprintf("redirect_to %s",$redirect_uri));

        $state         = $request->getState();
        //check user logged
        if (!$this->auth_service->isUserLogged()) {
            $this->memento_service->saveCurrentRequest();
            return $this->auth_strategy->doLogin($this->memento_service->getCurrentRequest());
        }


        $authorization_response = $this->auth_service->getUserAuthorizationResponse();
        if($authorization_response === IAuthService::AuthorizationResponse_None){
            $this->memento_service->saveCurrentRequest();
            return $this->auth_strategy->doConsent($this->memento_service->getCurrentRequest());
        }
        else if ($authorization_response === IAuthService::AuthorizationResponse_DenyOnce){
            throw new AccessDeniedException;
        }
        $response      = new OAuth2AuthorizationResponse();
        $token         = $this->token_service->getAuthorizationCode($client_id,$redirect_uri);

        if(is_null($token))
            throw new OAuth2GenericException("Invalid Token");

        $response->setAuthorizationCode($token->getValue());
        $response->setReturnTo($redirect_uri);
        //if state is present, return it on response
        if(!is_null($state))
            $response->setState($state);
        return $response;
    }



    public function getResponseType()
    {
        return OAuth2Protocol::OAuth2Protocol_ResponseType_Code;
    }

    public function getType()
    {
        // TODO: Implement getType() method.
    }

}