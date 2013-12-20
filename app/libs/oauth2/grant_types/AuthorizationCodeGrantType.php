<?php

namespace oauth2\grant_types;

use Exception;
use oauth2\exceptions\AccessDeniedException;
use oauth2\exceptions\InvalidAuthorizationCodeException;
use oauth2\exceptions\InvalidClientException;
use oauth2\exceptions\OAuth2GenericException;
use oauth2\exceptions\ScopeNotAllowedException;
use oauth2\exceptions\UnAuthorizedClientException;
use oauth2\exceptions\UnsupportedResponseTypeException;
use oauth2\exceptions\UriNotAllowedException;
use oauth2\models\IClient;
use oauth2\OAuth2Protocol;
use oauth2\requests\OAuth2Request;
use oauth2\responses\OAuth2AccessTokenResponse;
use oauth2\responses\OAuth2AuthorizationResponse;

use oauth2\services\IMementoOAuth2AuthenticationRequestService;
use oauth2\services\IClientService;
use oauth2\services\ITokenService;
use oauth2\strategies\IOAuth2AuthenticationStrategy;
use ReflectionClass;
use utils\services\IAuthService;

/**
 * Class AuthorizationCodeGrantType
 * Authorization Code Grant Implementation
 * http://tools.ietf.org/html/rfc6749#section-4.1
 * @package oauth2\grant_types
 */
class AuthorizationCodeGrantType implements IGrantType
{


    private $auth_service;
    private $auth_strategy;
    private $memento_service;

    public function __construct(IClientService $client_service, ITokenService $token_service, IAuthService $auth_service, IMementoOAuth2AuthenticationRequestService $memento_service, IOAuth2AuthenticationStrategy $auth_strategy)
    {
        parent::__construct($client_service,$token_service);
        $this->auth_service = $auth_service;
        $this->memento_service = $memento_service;
        $this->auth_strategy = $auth_strategy;
    }

    public function canHandle(OAuth2Request $request)
    {
        $reflector = new ReflectionClass($request);
        $class_name = $reflector->getName();
        return
            ($class_name == 'oauth2\requests\OAuth2AuthorizationRequest' && $request->isValid()) ||
            ($class_name == 'oauth2\requests\OAuth2AccessTokenRequest' && $request->isValid() && $request->getGrantType() == OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode);
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

        $reflector = new ReflectionClass($request);
        $class_name = $reflector->getName();
        if ($class_name == 'oauth2\requests\OAuth2AuthorizationRequest') {
            $client_id = $request->getClientId();

            $response_type = $request->getResponseType();

            if ($response_type !== OAuth2Protocol::OAuth2Protocol_ResponseType_Code)
                throw new UnsupportedResponseTypeException(sprintf("response_type %s", $response_type));

            $client = $this->client_service->getClientById($client_id);
            if (is_null($client))
                throw new InvalidClientException(sprintf("client_id %s", $client_id));

            if ($client->getClientType() !== IClient::ClientType_Confidential)
                throw new UnAuthorizedClientException();
            //check redirect uri
            $redirect_uri = $request->getRedirectUri();
            if (!$client->isUriAllowed($redirect_uri))
                throw new UriNotAllowedException(sprintf("redirect_to %s", $redirect_uri));

            //check requested scope
            $scope = $request->getScope();
            if (!$client->isScopeAllowed($scope))
                throw new ScopeNotAllowedException(sprintf("redirect_to %s", $redirect_uri));

            $state = $request->getState();
            //check user logged
            if (!$this->auth_service->isUserLogged()) {
                $this->memento_service->saveCurrentAuthorizationRequest();
                return $this->auth_strategy->doLogin($this->memento_service->getCurrentAuthorizationRequest());
            }


            $authorization_response = $this->auth_service->getUserAuthorizationResponse();
            if ($authorization_response === IAuthService::AuthorizationResponse_None) {
                $this->memento_service->saveCurrentAuthorizationRequest();
                return $this->auth_strategy->doConsent($this->memento_service->getCurrentAuthorizationRequest());
            } else if ($authorization_response === IAuthService::AuthorizationResponse_DenyOnce) {
                throw new AccessDeniedException;
            }
            $response = new OAuth2AuthorizationResponse();
            $token = $this->token_service->createAuthorizationCode($client_id, $scope, $redirect_uri);

            if (is_null($token))
                throw new OAuth2GenericException("Invalid Token");

            $response->setAuthorizationCode($token->getValue());
            $response->setReturnTo($redirect_uri);
            //if state is present, return it on response
            if (!is_null($state))
                $response->setState($state);
            return $response;
        }
        throw new Exception('Invalid Request Type');
    }

    /** Implementation of http://tools.ietf.org/html/rfc6749#section-4.1.3
     * @param OAuth2Request $request
     * @return OAuth2AccessTokenResponse
     * @throws \oauth2\exceptions\InvalidAuthorizationCodeException
     * @throws \oauth2\exceptions\ExpiredAuthorizationCodeException
     * @throws \Exception
     * @throws \oauth2\exceptions\InvalidClientException
     * @throws \oauth2\exceptions\UnAuthorizedClientException
     * @throws \oauth2\exceptions\UriNotAllowedException
     */
    public function completeFlow(OAuth2Request $request)
    {
        $reflector = new ReflectionClass($request);
        $class_name = $reflector->getName();
        if ($class_name == 'oauth2\requests\OAuth2AccessTokenRequest') {

            parent::completeFlow($request);

            //only confidential clients could use this grant type
            if ($this->current_client->getClientType() !== IClient::ClientType_Confidential)
                throw new UnAuthorizedClientException();



            $current_redirect_uri = $request->getRedirectUri();
            //verify redirect uri
            if (!$this->current_client->isUriAllowed($current_redirect_uri))
                throw new UriNotAllowedException();

            $code = $request->getCode();
            // verify that the authorization code is valid
            // The client MUST NOT use the authorization code
            // more than once.  If an authorization code is used more than
            // once, the authorization server MUST deny the request and SHOULD
            // revoke (when possible) all tokens previously issued based on
            // that authorization code.  The authorization code is bound to
            // the client identifier and redirection URI.
            $auth_code = $this->token_service->getAuthorizationCode($code);
            // verify that the authorization code is valid
            if (is_null($auth_code)) {
                throw new InvalidAuthorizationCodeException();
            }

            $client_id = $auth_code->getClientId();

            //ensure that the authorization code was issued to the authenticated
            //confidential client, or if the client is public, ensure that the
            //code was issued to "client_id" in the request
            if ($client_id !== $this->current_client_id)
                throw new UnAuthorizedClientException;


            // ensure that the "redirect_uri" parameter is present if the
            // "redirect_uri" parameter was included in the initial authorization
            // and if included ensure that their values are identical.
            $redirect_uri = $auth_code->getRedirectUri();
            if (!empty($redirect_uri) && $redirect_uri !== $current_redirect_uri)
                throw new UriNotAllowedException();

            $access_token = $this->token_service->createAccessToken($auth_code, $current_redirect_uri);
            //emits refresh token
            $refresh_token = null;
            if ($this->current_client->use_refresh_token)
                $refresh_token = $this->token_service->createRefreshToken($access_token);

            $response = new OAuth2AccessTokenResponse($access_token->getValue(), $access_token->getLifetime(), !is_null($refresh_token) ? $refresh_token->getValue() : null);
            return $response;

        }
        throw new Exception('Invalid Request Type');
    }

    public function getType()
    {
        return OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode;
    }
}