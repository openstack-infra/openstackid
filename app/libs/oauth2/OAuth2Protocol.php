<?php

namespace oauth2;

//endpoints
use jwa\JSONWebSignatureAndEncryptionAlgorithms;
use oauth2\endpoints\AuthorizationEndpoint;
use oauth2\endpoints\TokenEndpoint;
use oauth2\endpoints\TokenIntrospectionEndpoint;
use oauth2\endpoints\TokenRevocationEndpoint;

//exceptions
use Exception;
use oauth2\exceptions\AccessDeniedException;
use oauth2\exceptions\BearerTokenDisclosureAttemptException;
use oauth2\exceptions\ExpiredAuthorizationCodeException;
use oauth2\exceptions\InvalidAccessTokenException;
use oauth2\exceptions\InvalidApplicationType;
use oauth2\exceptions\InvalidAuthorizationCodeException;
use oauth2\exceptions\InvalidClientException;
use oauth2\exceptions\InvalidClientType;
use oauth2\exceptions\InvalidGrantTypeException;
use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\exceptions\LockedClientException;
use oauth2\exceptions\MissingClientIdParam;
use oauth2\exceptions\OAuth2GenericException;
use oauth2\exceptions\ReplayAttackException;
use oauth2\exceptions\ScopeNotAllowedException;
use oauth2\exceptions\UnAuthorizedClientException;
use oauth2\exceptions\UnsupportedResponseTypeException;
use oauth2\exceptions\UriNotAllowedException;
use oauth2\exceptions\MissingClientAuthorizationInfo;
use oauth2\exceptions\InvalidRedeemAuthCodeException;
use oauth2\exceptions\InvalidClientCredentials;
use oauth2\exceptions\ExpiredAccessTokenException;

//grant types
use oauth2\grant_types\AuthorizationCodeGrantType;
use oauth2\grant_types\ImplicitGrantType;
use oauth2\grant_types\RefreshBearerTokenGrantType;
use oauth2\grant_types\ClientCredentialsGrantType;

use oauth2\requests\OAuth2Request;

use oauth2\responses\OAuth2DirectErrorResponse;
use oauth2\responses\OAuth2IndirectErrorResponse;
use oauth2\responses\OAuth2TokenRevocationResponse;

use oauth2\services\IApiScopeService;
use oauth2\services\IClientService;
use oauth2\services\IMementoOAuth2AuthenticationRequestService;
use oauth2\services\ITokenService;
use oauth2\strategies\IOAuth2AuthenticationStrategy;
use oauth2\strategies\OAuth2IndirectErrorResponseFactoryMethod;
use oauth2\services\IUserConsentService;
use utils\services\IAuthService;
use utils\services\ICheckPointService;
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
    const OAuth2Protocol_GrantType_RefreshToken = 'refresh_token';
    const OAuth2Protocol_ResponseType_Code = 'code';
    const OAuth2Protocol_ResponseType_Token = 'token';
    const OAuth2Protocol_ResponseType = 'response_type';
    const OAuth2Protocol_ClientId = 'client_id';
    const OAuth2Protocol_UserId = 'user_id';
    const OAuth2Protocol_ClientSecret = 'client_secret';
    const OAuth2Protocol_Token = 'token';
    const OAuth2Protocol_TokenType = 'token_type';
    //http://tools.ietf.org/html/rfc7009#section-2.1
    const OAuth2Protocol_TokenType_Hint = 'token_type_hint';
    const OAuth2Protocol_AccessToken_ExpiresIn = 'expires_in';
    const OAuth2Protocol_RefreshToken = 'refresh_token';
    const OAuth2Protocol_AccessToken = 'access_token';
    const OAuth2Protocol_RedirectUri = 'redirect_uri';
    const OAuth2Protocol_Scope = 'scope';
    const OAuth2Protocol_Audience = 'audience';
    const OAuth2Protocol_State = 'state';
    /**
     * Indicates whether the user should be re-prompted for consent. The default is auto,
     * so a given user should only see the consent page for a given set of scopes the first time
     * through the sequence. If the value is force, then the user sees a consent page even if they
     * previously gave consent to your application for a given set of scopes.
     */
    const OAuth2Protocol_Approval_Prompt = 'approval_prompt';
    const OAuth2Protocol_Approval_Prompt_Force = 'force';
    const OAuth2Protocol_Approval_Prompt_Auto = 'auto';

    /**
     * Indicates whether your application needs to access an API when the user is not present at
     * the browser. This parameter defaults to online. If your application needs to refresh access tokens
     * when the user is not present at the browser, then use offline. This will result in your application
     * obtaining a refresh token the first time your application exchanges an authorization code for a user.
     */
    const OAuth2Protocol_AccessType = 'access_type';
    const OAuth2Protocol_AccessType_Online = 'online';
    const OAuth2Protocol_AccessType_Offline = 'offline';

    const OAuth2Protocol_GrantType = 'grant_type';
    const OAuth2Protocol_Error = 'error';
    const OAuth2Protocol_ErrorDescription = 'error_description';
    const OAuth2Protocol_ErrorUri = 'error_uri';
    const OAuth2Protocol_Error_InvalidRequest = 'invalid_request';
    const OAuth2Protocol_Error_UnauthorizedClient = 'unauthorized_client';
    const OAuth2Protocol_Error_AccessDenied = 'access_denied';
    const OAuth2Protocol_Error_UnsupportedResponseType = 'unsupported_response_type';
    const OAuth2Protocol_Error_InvalidScope = 'invalid_scope';
    const OAuth2Protocol_Error_UnsupportedGrantType = 'unsupported_grant_type';
    const OAuth2Protocol_Error_InvalidGrant = 'invalid_grant';
    //error codes definitions http://tools.ietf.org/html/rfc6749#section-4.1.2.1
    const OAuth2Protocol_Error_ServerError = 'server_error';
    const OAuth2Protocol_Error_TemporallyUnavailable = 'temporally_unavailable';
    //http://tools.ietf.org/html/rfc7009#section-2.2.1
    const OAuth2Protocol_Error_Unsupported_TokenType = 'unsupported_token_type';
    //http://tools.ietf.org/html/rfc6750#section-3-1
    const OAuth2Protocol_Error_InvalidToken = 'invalid_token';
    const OAuth2Protocol_Error_InsufficientScope = 'insufficient_scope';

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

    // http://openid.net/specs/openid-connect-core-1_0.html#ClientAuthentication

    const TokenEndpoint_AuthMethod_ClientSecretBasic = 'client_secret_basic';
    const TokenEndpoint_AuthMethod_ClientSecretPost  = 'client_secret_post';
    const TokenEndpoint_AuthMethod_ClientSecretJwt   = 'client_secret_jwt';
    const TokenEndpoint_AuthMethod_PrivateKeyJwt     = 'private_key_jwt';
    const TokenEndpoint_AuthMethod_None              = 'none';

    public static $token_endpoint_auth_methods = array(
        self::TokenEndpoint_AuthMethod_ClientSecretBasic,
        self::TokenEndpoint_AuthMethod_ClientSecretPost,
        self::TokenEndpoint_AuthMethod_ClientSecretJwt,
        self::TokenEndpoint_AuthMethod_PrivateKeyJwt,
    );


    public static $supported_signing_algorithms = array(
        JSONWebSignatureAndEncryptionAlgorithms::HS256,
        JSONWebSignatureAndEncryptionAlgorithms::HS384,
        JSONWebSignatureAndEncryptionAlgorithms::HS512,
        JSONWebSignatureAndEncryptionAlgorithms::RS256,
        JSONWebSignatureAndEncryptionAlgorithms::RS384,
        JSONWebSignatureAndEncryptionAlgorithms::RS512,
        JSONWebSignatureAndEncryptionAlgorithms::PS256,
        JSONWebSignatureAndEncryptionAlgorithms::PS384,
        JSONWebSignatureAndEncryptionAlgorithms::PS512,
        JSONWebSignatureAndEncryptionAlgorithms::None
    );

    public static $supported_key_management_algorithms = array(
        JSONWebSignatureAndEncryptionAlgorithms::RSA1_5,
        JSONWebSignatureAndEncryptionAlgorithms::RSA_OAEP,
        JSONWebSignatureAndEncryptionAlgorithms::RSA_OAEP_256,
        JSONWebSignatureAndEncryptionAlgorithms::None,
    );

    public static $supported_content_encryption_algorithms = array(
        JSONWebSignatureAndEncryptionAlgorithms::A128CBC_HS256,
        JSONWebSignatureAndEncryptionAlgorithms::A192CBC_HS384,
        JSONWebSignatureAndEncryptionAlgorithms::A256CBC_HS512,
        JSONWebSignatureAndEncryptionAlgorithms::None,
    );


    /**
     * http://tools.ietf.org/html/rfc6749#appendix-A
     * VSCHAR     = %x20-7E
     */
    const VsChar = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz.-_~';

    //services
    private $log_service;
    private $checkpoint_service;
    private $client_service;

    //endpoints
    private $authorize_endpoint;
    private $token_endpoint;
    private $revoke_endpoint;
    private $introspection_endpoint;

    //grant types
    private $grant_types = array();

    public function __construct(
        ILogService    $log_service,
        IClientService $client_service,
        ITokenService  $token_service,
        IAuthService   $auth_service,
        IMementoOAuth2AuthenticationRequestService $memento_service,
        IOAuth2AuthenticationStrategy $auth_strategy,
        ICheckPointService $checkpoint_service,
        IApiScopeService   $scope_service,
        IUserConsentService $user_consent_service)
    {

        $authorization_code_grant_type    = new AuthorizationCodeGrantType($scope_service, $client_service, $token_service, $auth_service, $memento_service, $auth_strategy, $log_service,$user_consent_service);
        $implicit_grant_type              = new ImplicitGrantType($scope_service, $client_service, $token_service, $auth_service, $memento_service, $auth_strategy, $log_service,$user_consent_service);
        $refresh_bearer_token_grant_type  = new RefreshBearerTokenGrantType($client_service, $token_service, $log_service);
        $client_credential_grant_type     = new ClientCredentialsGrantType($scope_service,$client_service, $token_service, $log_service);

        $this->grant_types[$authorization_code_grant_type->getType()]   = $authorization_code_grant_type;
        $this->grant_types[$implicit_grant_type->getType()]             = $implicit_grant_type;
        $this->grant_types[$refresh_bearer_token_grant_type->getType()] = $refresh_bearer_token_grant_type;
        $this->grant_types[$client_credential_grant_type->getType()]    = $client_credential_grant_type;


        $this->log_service                = $log_service;
        $this->checkpoint_service         = $checkpoint_service;
        $this->client_service             = $client_service;

        $this->authorize_endpoint         = new AuthorizationEndpoint($this);
        $this->token_endpoint             = new TokenEndpoint($this);
        $this->revoke_endpoint            = new TokenRevocationEndpoint($this,$client_service, $token_service, $log_service);
        $this->introspection_endpoint     = new TokenIntrospectionEndpoint($this,$client_service, $token_service, $log_service);
    }

    /**
     * @param OAuth2Request $request
     * @return mixed|OAuth2IndirectErrorResponse
     * @throws \Exception
     * @throws exceptions\UriNotAllowedException
     */
    public function authorize(OAuth2Request $request = null)
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

            return OAuth2IndirectErrorResponseFactoryMethod::buildResponse($request, OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest, $redirect_uri);
        } catch (UnsupportedResponseTypeException $ex2) {
            $this->log_service->error($ex2);
            $this->checkpoint_service->trackException($ex2);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex2;

            return OAuth2IndirectErrorResponseFactoryMethod::buildResponse($request, OAuth2Protocol::OAuth2Protocol_Error_UnsupportedResponseType, $redirect_uri);
        } catch (InvalidClientException $ex3) {
            $this->log_service->error($ex3);
            $this->checkpoint_service->trackException($ex3);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex3;

            return OAuth2IndirectErrorResponseFactoryMethod::buildResponse($request, OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient, $redirect_uri);
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

            return OAuth2IndirectErrorResponseFactoryMethod::buildResponse($request, OAuth2Protocol::OAuth2Protocol_Error_InvalidScope, $redirect_uri);
        } catch (UnAuthorizedClientException $ex6) {
            $this->log_service->error($ex6);
            $this->checkpoint_service->trackException($ex6);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex6;

            return OAuth2IndirectErrorResponseFactoryMethod::buildResponse($request, OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient, $redirect_uri);
        } catch (AccessDeniedException $ex7) {
            $this->log_service->error($ex7);
            $this->checkpoint_service->trackException($ex7);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex7;

            return OAuth2IndirectErrorResponseFactoryMethod::buildResponse($request, OAuth2Protocol::OAuth2Protocol_Error_AccessDenied, $redirect_uri);
        } catch (OAuth2GenericException $ex8) {
            $this->log_service->error($ex8);
            $this->checkpoint_service->trackException($ex8);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex8;

            return OAuth2IndirectErrorResponseFactoryMethod::buildResponse($request, OAuth2Protocol::OAuth2Protocol_Error_ServerError, $redirect_uri);
        }
        catch(InvalidApplicationType $ex9){
            $this->log_service->error($ex9);
            $this->checkpoint_service->trackException($ex9);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex9;

            return OAuth2IndirectErrorResponseFactoryMethod::buildResponse($request, OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient, $redirect_uri);
        }
        catch(LockedClientException $ex10){
            $this->log_service->error($ex10);
            $this->checkpoint_service->trackException($ex10);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex10;

            return OAuth2IndirectErrorResponseFactoryMethod::buildResponse($request, OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient, $redirect_uri);
        }
        catch(MissingClientIdParam $ex11){
            $this->log_service->error($ex11);
            $this->checkpoint_service->trackException($ex11);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex11;

            return OAuth2IndirectErrorResponseFactoryMethod::buildResponse($request, OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient, $redirect_uri);
        }
        catch(InvalidClientType $ex12){
            $this->log_service->error($ex12);
            $this->checkpoint_service->trackException($ex12);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex12;

            return OAuth2IndirectErrorResponseFactoryMethod::buildResponse($request, OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient, $redirect_uri);
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            $this->checkpoint_service->trackException($ex);

            $redirect_uri = $this->validateRedirectUri($request);
            if (is_null($redirect_uri))
                throw $ex;

            return OAuth2IndirectErrorResponseFactoryMethod::buildResponse($request, OAuth2Protocol::OAuth2Protocol_Error_ServerError, $redirect_uri);
        }
    }

    private function validateRedirectUri(OAuth2Request $request = null)
    {
        if (is_null($request))
            return null;
        $redirect_uri = $request->getRedirectUri();
        if (is_null($redirect_uri))
            return null;
        $client_id = $request->getClientId();
        if (is_null($client_id))
            return null;
        $client = $this->client_service->getClientById($client_id);
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
    public function token(OAuth2Request $request = null)
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
        }
        catch(ScopeNotAllowedException $ex11){
            $this->log_service->error($ex11);
            $this->checkpoint_service->trackException($ex11);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_InvalidScope);
        }
        catch(InvalidApplicationType $ex12){
            $this->log_service->error($ex12);
            $this->checkpoint_service->trackException($ex12);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient);
        }
        catch(LockedClientException $ex13){
            $this->log_service->error($ex13);
            $this->checkpoint_service->trackException($ex13);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient);
        }
        catch(MissingClientIdParam $ex14){
            $this->log_service->error($ex14);
            $this->checkpoint_service->trackException($ex14);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient);
        }
        catch(InvalidClientType $ex15){
            $this->log_service->error($ex15);
            $this->checkpoint_service->trackException($ex15);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient);
        }
        catch(MissingClientAuthorizationInfo $ex16){
            $this->log_service->error($ex16);
            $this->checkpoint_service->trackException($ex16);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient);
        }
        catch(InvalidRedeemAuthCodeException $ex17){
            $this->log_service->error($ex17);
            $this->checkpoint_service->trackException($ex17);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient);
        }
        catch(InvalidClientCredentials $ex18){
            $this->log_service->error($ex18);
            $this->checkpoint_service->trackException($ex18);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient);
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            $this->checkpoint_service->trackException($ex);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_ServerError);
        }
    }

    /**
     * Revoke Token Endpoint
     * http://tools.ietf.org/html/rfc7009
     * @param OAuth2Request $request
     * @return mixed
     */
    public function revoke(OAuth2Request $request = null){

        try {
            if (is_null($request) || !$request->isValid())
                throw new InvalidOAuth2Request;
            return $this->revoke_endpoint->handle($request);
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            $this->checkpoint_service->trackException($ex);
            //simple say "OK" and be on our way ...
            return new OAuth2TokenRevocationResponse;
        }
    }

    /**
     * Introspection Token Endpoint
     * http://tools.ietf.org/html/draft-richer-oauth-introspection-04
     * @param OAuth2Request $request
     * @return mixed
     */
    public function introspection(OAuth2Request $request = null){

        try {
            if (is_null($request) || !$request->isValid())
                throw new InvalidOAuth2Request;
            return $this->introspection_endpoint->handle($request);
        }
        catch(UnAuthorizedClientException $ex1){
            $this->log_service->error($ex1);
            $this->checkpoint_service->trackException($ex1);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient);
        }
        catch(BearerTokenDisclosureAttemptException $ex2){
            $this->log_service->error($ex2);
            $this->checkpoint_service->trackException($ex2);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_InvalidGrant);
        }
        catch(InvalidClientCredentials $ex3){
            $this->log_service->error($ex3);
            $this->checkpoint_service->trackException($ex3);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient);
        }
        catch(ExpiredAccessTokenException $ex4){
            $this->log_service->warning($ex4);
            $this->checkpoint_service->trackException($ex4);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_InvalidToken);
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            $this->checkpoint_service->trackException($ex);
            return new OAuth2DirectErrorResponse(OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest);
        }
    }

    public function getAvailableGrants()
    {
        return $this->grant_types;
    }
}