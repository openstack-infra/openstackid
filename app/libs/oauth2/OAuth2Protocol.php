<?php

namespace oauth2;

use oauth2\requests\OAuth2Request;
use oauth2\endpoints\AuthorizationEndpoint;
use oauth2\endpoints\TokenEndpoint;
use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\exceptions\InvalidClientException;
use oauth2\exceptions\UriNotAllowedException;
use oauth2\exceptions\ScopeNotAllowedException;
use oauth2\exceptions\UnsupportedResponseTypeException;
use oauth2\exceptions\UnAuthorizedClientException;
use oauth2\exceptions\OAuth2GenericException;
use oauth2\exceptions\AccessDeniedException;
use Exception;
use oauth2\responses\OAuth2DirectErrorResponse;
use oauth2\responses\OAuth2DirectResponse;
use oauth2\responses\OAuth2IndirectErrorResponse;
use utils\services\ILogService;
use oauth2\services\IClientService;
use oauth2\services\IMementoOAuth2AuthenticationRequestService;
use oauth2\services\ITokenService;
use utils\services\IAuthService;
use oauth2\strategies\IOAuth2AuthenticationStrategy;

/**
 * Class OAuth2Protocol
 * Implementation of http://tools.ietf.org/html/rfc6749
 * @package oauth2
 */
class OAuth2Protocol implements  IOAuth2Protocol{

    private $log_service;
    public function __construct( ILogService $log_service,
                                IClientService $client_service,
                                ITokenService $token_service,
                                IAuthService $auth_service,
                                IMementoOAuth2AuthenticationRequestService $memento_service,
                                IOAuth2AuthenticationStrategy $auth_strategy)
    {
        $this->log_service = $log_service;
        $this->authorize_endpoint = new AuthorizationEndpoint($client_service,$token_service,$auth_service,$memento_service,$auth_strategy);
        $this->token_endpoint     = new TokenEndpoint;
    }

    private $authorize_endpoint;
    private $token_endpoint;

    const OAuth2Protocol_GrantType_AuthCode               = 'authorization_code';
    const OAuth2Protocol_GrantType_Implicit               = 'implicit';
    const OAuth2Protocol_GrantType_ResourceOwner_Password = 'password';
    const OAuth2Protocol_GrantType_ClientCredentials      = 'client_credentials';

    const OAuth2Protocol_ResponseType_Code  = 'code';
    const OAuth2Protocol_ResponseType_Token = 'token';

    public static $valid_responses_types = array(
        self::OAuth2Protocol_ResponseType_Code =>self::OAuth2Protocol_ResponseType_Code,
        self::OAuth2Protocol_ResponseType_Token => self::OAuth2Protocol_ResponseType_Token
    );

    const OAuth2Protocol_ResponseType                  = "response_type";
    const OAuth2Protocol_ClientId                      = "client_id";
    const OAuth2Protocol_RedirectUri                   = "redirect_uri";
    const OAuth2Protocol_Scope                         = "scope";
    const OAuth2Protocol_State                         = "state";
    const OAuth2Protocol_Error                         = "error";
    const OAuth2Protocol_ErrorDescription              = "error_description";
    const OAuth2Protocol_ErrorUri                      = "error_uri";

    //error codes definitions http://tools.ietf.org/html/rfc6749#section-4.1.2.1

    const OAuth2Protocol_Error_InvalidRequest          = "invalid_request";
    const OAuth2Protocol_Error_UnauthorizedClient      = "unauthorized_client";
    const OAuth2Protocol_Error_AccessDenied            = "access_denied";
    const OAuth2Protocol_Error_UnsupportedResponseType = "unsupported_response_type";
    const OAuth2Protocol_Error_InvalidScope            = "invalid_scope";
    const OAuth2Protocol_Error_ServerError             = "server_error";
    const OAuth2Protocol_Error_TemporallyUnavailable   = "temporally_unavailable";

    public static $protocol_definition = array(
        self::OAuth2Protocol_ResponseType => self::OAuth2Protocol_ResponseType,
        self::OAuth2Protocol_ClientId     => self::OAuth2Protocol_ClientId,
        self::OAuth2Protocol_RedirectUri  => self::OAuth2Protocol_RedirectUri,
        self::OAuth2Protocol_Scope        => self::OAuth2Protocol_Scope,
        self::OAuth2Protocol_State        => self::OAuth2Protocol_State
    );


    /**
     * @param OAuth2Request $request
     * @return mixed|OAuth2IndirectErrorResponse
     * @throws \Exception
     * @throws exceptions\UriNotAllowedException
     */
    public function authorize(OAuth2Request $request)
    {
        try{
            if (is_null($request) || !$request->isValid())
                throw new InvalidOAuth2Request;
            return $this->authorize_endpoint->handle($request);
        }
        catch(InvalidOAuth2Request $ex1){
            $this->log_service->error($ex1);
            return new OAuth2IndirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest, $request->getRedirectUri());
        }
        catch(UnsupportedResponseTypeException $ex2){
            $this->log_service->error($ex2);
            return new OAuth2IndirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnsupportedResponseType, $request->getRedirectUri());
        }
        catch(InvalidClientException $ex3){
            $this->log_service->error($ex3);
            return new OAuth2IndirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient, $request->getRedirectUri());
        }
        catch(UriNotAllowedException $ex4){
            $this->log_service->error($ex4);
            throw $ex4;
        }
        catch(ScopeNotAllowedException $ex5){
            $this->log_service->error($ex5);
            return new OAuth2IndirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_InvalidScope, $request->getRedirectUri());
        }
        catch(UnAuthorizedClientException $ex6){
            $this->log_service->error($ex6);
            return new OAuth2IndirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient, $request->getRedirectUri());
        }
        catch(AccessDeniedException $ex7){
            $this->log_service->error($ex7);
            return new OAuth2IndirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_AccessDenied, $request->getRedirectUri());
        }
        catch(OAuth2GenericException $ex8){
            $this->log_service->error($ex8);
            return new OAuth2IndirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_ServerError, $request->getRedirectUri());
        }
        catch(Exception $ex){
            $this->log_service->error($ex);
            return new OAuth2IndirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_ServerError, $request->getRedirectUri());
        }
    }

    /**
     * @param OAuth2Request $request
     * @return OAuth2DirectErrorResponse|void
     */
    public function token(OAuth2Request $request)
    {
        try{
            if (is_null($request) || !$request->isValid())
                throw new InvalidOAuth2Request;
            return $this->token_endpoint->handle($request);
        }
        catch(InvalidOAuth2Request $ex1){
            $this->log_service->error($ex1);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest);
        }
    }
}