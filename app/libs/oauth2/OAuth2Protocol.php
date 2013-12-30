<?php

namespace oauth2;

use Exception;
use oauth2\endpoints\AuthorizationEndpoint;
use oauth2\endpoints\TokenEndpoint;
use oauth2\exceptions\AccessDeniedException;
use oauth2\exceptions\BearerTokenDisclosureAttemptException;
use oauth2\exceptions\ExpiredAuthorizationCodeException;
use oauth2\exceptions\InvalidAccessTokenException;
use oauth2\exceptions\InvalidAuthorizationCodeException;
use oauth2\exceptions\InvalidClientException;
use oauth2\exceptions\InvalidGrantTypeException;
use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\exceptions\OAuth2GenericException;
use oauth2\exceptions\ReplayAttackException;
use oauth2\exceptions\ScopeNotAllowedException;
use oauth2\exceptions\UnAuthorizedClientException;
use oauth2\exceptions\UnsupportedResponseTypeException;

use oauth2\exceptions\UriNotAllowedException;
use oauth2\grant_types\AuthorizationCodeGrantType;
use oauth2\grant_types\ValidateBearerTokenGrantType;

use oauth2\requests\OAuth2Request;
use oauth2\responses\OAuth2DirectErrorResponse;

use oauth2\responses\OAuth2IndirectErrorResponse;
use oauth2\services\IClientService;
use oauth2\services\IMementoOAuth2AuthenticationRequestService;
use oauth2\services\ITokenService;
use oauth2\strategies\IOAuth2AuthenticationStrategy;
use utils\services\IAuthService;
use utils\services\ICheckPointService;


//grant types

use utils\services\ILogService;

/**
 * Class OAuth2Protocol
 * Implementation of http://tools.ietf.org/html/rfc6749
 * @package oauth2
 */
class OAuth2Protocol implements IOAuth2Protocol
{

    const OAuth2Protocol_GrantType_AuthCode = 'authorization_code';
    const OAuth2Protocol_GrantType_Implicit = 'implicit';
    const OAuth2Protocol_GrantType_ResourceOwner_Password = 'password';
    const OAuth2Protocol_GrantType_ClientCredentials = 'client_credentials';
    const OAuth2Protocol_ResponseType_Code = 'code';
    const OAuth2Protocol_ResponseType_Token = 'token';
    const OAuth2Protocol_ResponseType = "response_type";
    const OAuth2Protocol_ClientId = "client_id";
    const OAuth2Protocol_ClientSecret = "client_secret";
    const OAuth2Protocol_AccessToken = "access_token";
    const OAuth2Protocol_Token = "token";
    const OAuth2Protocol_TokenType = "token_type";
    const OAuth2Protocol_AccessToken_ExpiresIn = "expires_in";
    const OAuth2Protocol_RefreshToken = "refresh_token";
    const OAuth2Protocol_RedirectUri = "redirect_uri";
    const OAuth2Protocol_Scope = "scope";
    const OAuth2Protocol_Audience = "audience";
    const OAuth2Protocol_State = "state";
    const OAuth2Protocol_GrantType = 'grant_type';
    const OAuth2Protocol_Error = "error";
    const OAuth2Protocol_ErrorDescription = "error_description";
    const OAuth2Protocol_ErrorUri = "error_uri";
    const OAuth2Protocol_Error_InvalidRequest = "invalid_request";
    const OAuth2Protocol_Error_UnauthorizedClient = "unauthorized_client";
    const OAuth2Protocol_Error_AccessDenied = "access_denied";
    const OAuth2Protocol_Error_UnsupportedResponseType = "unsupported_response_type";
    const OAuth2Protocol_Error_InvalidScope = "invalid_scope";
    const OAuth2Protocol_Error_UnsupportedGrantType = "unsupported_grant_type";
    const OAuth2Protocol_Error_InvalidGrant = "invalid_grant";

    //error codes definitions http://tools.ietf.org/html/rfc6749#section-4.1.2.1
    const OAuth2Protocol_Error_ServerError = "server_error";
    const OAuth2Protocol_Error_TemporallyUnavailable = "temporally_unavailable";
    public static $valid_responses_types = array(
        self::OAuth2Protocol_ResponseType_Code => self::OAuth2Protocol_ResponseType_Code,
        self::OAuth2Protocol_ResponseType_Token => self::OAuth2Protocol_ResponseType_Token
    );
    public static $protocol_definition = array(
        self::OAuth2Protocol_ResponseType => self::OAuth2Protocol_ResponseType,
        self::OAuth2Protocol_ClientId => self::OAuth2Protocol_ClientId,
        self::OAuth2Protocol_RedirectUri => self::OAuth2Protocol_RedirectUri,
        self::OAuth2Protocol_Scope => self::OAuth2Protocol_Scope,
        self::OAuth2Protocol_State => self::OAuth2Protocol_State
    );

    //services
    private $log_service;
    private $checkpoint_service;
    private $client_service;

    //endpoints
    private $authorize_endpoint;
    private $token_endpoint;

    //grant types
    private $grant_types = array();

    public function __construct(
        ILogService $log_service,
        IClientService $client_service,
        ITokenService $token_service,
        IAuthService $auth_service,
        IMementoOAuth2AuthenticationRequestService $memento_service,
        IOAuth2AuthenticationStrategy $auth_strategy,
        ICheckPointService $checkpoint_service)
    {

        //todo: add dynamic creation logic (configure grants types from db)

        $authorization_code_grant_type = new AuthorizationCodeGrantType($client_service, $token_service, $auth_service, $memento_service, $auth_strategy, $log_service);
        $validate_bearer_token_grant_type = new ValidateBearerTokenGrantType($client_service, $token_service, $log_service);
        $this->grant_types[$authorization_code_grant_type->getType()] = $authorization_code_grant_type;
        $this->grant_types[$validate_bearer_token_grant_type->getType()] = $validate_bearer_token_grant_type;

        $this->log_service = $log_service;
        $this->checkpoint_service = $checkpoint_service;
        $this->client_service = $client_service;

        $this->authorize_endpoint = new AuthorizationEndpoint($this);
        $this->token_endpoint = new TokenEndpoint($this);
    }

    /**
     * @param OAuth2Request $request
     * @return mixed|OAuth2IndirectErrorResponse
     * @throws \Exception
     * @throws exceptions\UriNotAllowedException
     */
    public function authorize(OAuth2Request $request)
    {
        try {
            if (is_null($request) || !$request->isValid())
                throw new InvalidOAuth2Request;
            return $this->authorize_endpoint->handle($request);
        } catch (InvalidOAuth2Request $ex1) {
            $this->log_service->error($ex1);
            $this->checkpoint_service->trackException($ex1);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex1;

            return new OAuth2IndirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest, $redirect_uri);
        } catch (UnsupportedResponseTypeException $ex2) {
            $this->log_service->error($ex2);
            $this->checkpoint_service->trackException($ex2);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex2;

            return new OAuth2IndirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnsupportedResponseType, $redirect_uri);
        } catch (InvalidClientException $ex3) {
            $this->log_service->error($ex3);
            $this->checkpoint_service->trackException($ex3);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex3;

            return new OAuth2IndirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient, $redirect_uri);
        } catch (UriNotAllowedException $ex4) {
            $this->log_service->error($ex4);
            $this->checkpoint_service->trackException($ex4);
            throw $ex4;
        } catch (ScopeNotAllowedException $ex5) {
            $this->log_service->error($ex5);
            $this->checkpoint_service->trackException($ex5);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex5;

            return new OAuth2IndirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_InvalidScope, $redirect_uri);
        } catch (UnAuthorizedClientException $ex6) {
            $this->log_service->error($ex6);
            $this->checkpoint_service->trackException($ex6);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex6;

            return new OAuth2IndirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient, $redirect_uri);
        } catch (AccessDeniedException $ex7) {
            $this->log_service->error($ex7);
            $this->checkpoint_service->trackException($ex7);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex7;

            return new OAuth2IndirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_AccessDenied, $redirect_uri);
        } catch (OAuth2GenericException $ex8) {
            $this->log_service->error($ex8);
            $this->checkpoint_service->trackException($ex8);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex8;

            return new OAuth2IndirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_ServerError, $redirect_uri);
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            $this->checkpoint_service->trackException($ex);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex;

            return new OAuth2IndirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_ServerError, $redirect_uri);
        }
    }

    private function validateRedirectUri(OAuth2Request $request)
    {
        $redirect_uri = $request->getRedirectUri();
        if (is_null($redirect_uri))
            return null;
        $client_id = $request->getClientId();
        if (is_null($client_id))
            return null;
        $client = $this->client_service->getClientByIdentifier($client_id);
        if (is_null($client))
            return null;
        if (!$client->isUriAllowed($redirect_uri))
            return null;
        return $redirect_uri;
    }

    /**
     * @param OAuth2Request $request
     * @return OAuth2DirectErrorResponse|void
     */
    public function token(OAuth2Request $request)
    {
        try {
            if (is_null($request) || !$request->isValid())
                throw new InvalidOAuth2Request;
            return $this->token_endpoint->handle($request);
        } catch (InvalidOAuth2Request $ex1) {
            $this->log_service->error($ex1);
            $this->checkpoint_service->trackException($ex1);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest);
        } catch (InvalidAuthorizationCodeException $ex2) {
            $this->log_service->error($ex2);
            $this->checkpoint_service->trackException($ex2);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient);
        } catch (InvalidClientException $ex3) {
            $this->log_service->error($ex3);
            $this->checkpoint_service->trackException($ex3);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient);
        } catch (UriNotAllowedException $ex4) {
            $this->log_service->error($ex4);
            $this->checkpoint_service->trackException($ex4);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient);
        } catch (UnAuthorizedClientException $ex5) {
            $this->log_service->error($ex5);
            $this->checkpoint_service->trackException($ex5);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient);
        } catch (ExpiredAuthorizationCodeException $ex6) {
            $this->log_service->error($ex6);
            $this->checkpoint_service->trackException($ex6);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest);
        } catch (ReplayAttackException $ex7) {
            $this->log_service->error($ex7);
            $this->checkpoint_service->trackException($ex7);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest);
        } catch (InvalidAccessTokenException $ex8) {
            $this->log_service->error($ex8);
            $this->checkpoint_service->trackException($ex8);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_InvalidGrant);
        } catch (InvalidGrantTypeException $ex9) {
            $this->log_service->error($ex9);
            $this->checkpoint_service->trackException($ex9);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_InvalidGrant);
        } catch (BearerTokenDisclosureAttemptException $ex10) {
            $this->log_service->error($ex10);
            $this->checkpoint_service->trackException($ex10);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_InvalidGrant);
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            $this->checkpoint_service->trackException($ex);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_ServerError);
        }
    }

    public function getAvailableGrants()
    {
        return $this->grant_types;
    }
}