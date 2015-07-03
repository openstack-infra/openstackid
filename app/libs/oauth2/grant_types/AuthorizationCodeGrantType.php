<?php

namespace oauth2\grant_types;

use Exception;
use oauth2\exceptions\AccessDeniedException;
use oauth2\exceptions\InvalidApplicationType;
use oauth2\exceptions\InvalidAuthorizationCodeException;
use oauth2\exceptions\InvalidClientException;
use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\exceptions\InvalidRedeemAuthCodeException;
use oauth2\exceptions\LockedClientException;
use oauth2\exceptions\OAuth2GenericException;
use oauth2\exceptions\ScopeNotAllowedException;
use oauth2\exceptions\UnsupportedResponseTypeException;
use oauth2\exceptions\UriNotAllowedException;
use oauth2\models\AuthorizationCode;
use oauth2\models\IClient;
use oauth2\OAuth2Protocol;
use oauth2\requests\OAuth2AccessTokenRequestAuthCode;
use oauth2\requests\OAuth2AuthenticationRequest;
use oauth2\requests\OAuth2AuthorizationRequest;
use oauth2\requests\OAuth2Request;
use oauth2\requests\OAuth2TokenRequest;
use oauth2\responses\OAuth2AccessTokenResponse;
use oauth2\responses\OAuth2AuthorizationResponse;
use oauth2\responses\OAuth2IdTokenResponse;
use oauth2\services\IApiScopeService;
use oauth2\services\IClientService;
use oauth2\services\IMementoOAuth2SerializerService;
use oauth2\services\ITokenService;
use oauth2\services\IUserConsentService;
use oauth2\strategies\IOAuth2AuthenticationStrategy;
use utils\services\IAuthService;
use utils\services\ILogService;
use oauth2\exceptions\InteractionRequiredException;
use oauth2\exceptions\LoginRequiredException;
use oauth2\exceptions\ConsentRequiredException;

/**
 * Class AuthorizationCodeGrantType
 * Authorization Code Grant Implementation
 * The authorization code grant type is used to obtain both access
 * tokens and refresh tokens and is optimized for confidential clients.
 * Since this is a redirection-based flow, the client must be capable of
 * interacting with the resource owner's user-agent (typically a web
 * browser) and capable of receiving incoming requests (via redirection)
 * from the authorization server.
 * http://tools.ietf.org/html/rfc6749#section-4.1
 * @package oauth2\grant_types
 */
class AuthorizationCodeGrantType extends AbstractGrantType
{
    /**
     * @var IAuthService
     */
    private $auth_service;
    /**
     * @var IOAuth2AuthenticationStrategy
     */
    private $auth_strategy;
    /**
     * @var IApiScopeService
     */
    private $scope_service;
    /**
     * @var IUserConsentService
     */
    private $user_consent_service;
    /**
     * @var IMementoOAuth2SerializerService
     */
    private $memento_service;

    /**
     * @param OAuth2Request $request
     * @return bool
     */
    public function canHandle(OAuth2Request $request)
    {

        if ($request instanceof OAuth2AuthorizationRequest && $request->isValid() && $request->getResponseType() == $this->getResponseType()) {
            return true;
        }
        if ($request instanceof OAuth2TokenRequest && $request->isValid() && $request->getGrantType() == $this->getType()) {
            return true;
        }

        return false;
    }

    /**
     * @param IApiScopeService $scope_service
     * @param IClientService $client_service
     * @param ITokenService $token_service
     * @param IAuthService $auth_service
     * @param IOAuth2AuthenticationStrategy $auth_strategy
     * @param ILogService $log_service
     * @param IUserConsentService $user_consent_service
     * @param IMementoOAuth2SerializerService $memento_service
     */
    public function __construct
    (
        IApiScopeService $scope_service,
        IClientService $client_service,
        ITokenService $token_service,
        IAuthService $auth_service,
        IOAuth2AuthenticationStrategy $auth_strategy,
        ILogService $log_service,
        IUserConsentService $user_consent_service,
        IMementoOAuth2SerializerService $memento_service
    )
    {

        parent::__construct($client_service, $token_service, $log_service);

        $this->user_consent_service = $user_consent_service;
        $this->scope_service = $scope_service;
        $this->auth_service = $auth_service;
        $this->auth_strategy = $auth_strategy;
        $this->memento_service = $memento_service;
    }

    /**
     * @return mixed|string
     */
    public function getType()
    {
        return OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode;
    }

    /** Implements first request processing for Authorization code (Authorization Request processing)
     * http://tools.ietf.org/html/rfc6749#section-4.1.1 and
     * http://tools.ietf.org/html/rfc6749#section-4.1.2
     * @param OAuth2Request $request
     * @return mixed|OAuth2AuthorizationResponse
     * @throws \oauth2\exceptions\UnsupportedResponseTypeException
     * @throws \oauth2\exceptions\LockedClientException
     * @throws \oauth2\exceptions\InvalidClientException
     * @throws \oauth2\exceptions\ScopeNotAllowedException
     * @throws \oauth2\exceptions\OAuth2GenericException
     * @throws \oauth2\exceptions\InvalidApplicationType
     * @throws \oauth2\exceptions\AccessDeniedException
     * @throws \oauth2\exceptions\UriNotAllowedException
     * @throws \oauth2\exceptions\InvalidOAuth2Request
     */
    public function handle(OAuth2Request $request)
    {

        if (!($request instanceof OAuth2AuthorizationRequest))
        {
            throw new InvalidOAuth2Request;
        }

        $client_id     = $request->getClientId();
        $response_type = $request->getResponseType();

        if ($response_type !== $this->getResponseType())
        {
            throw new UnsupportedResponseTypeException(sprintf("response_type %s", $response_type));
        }

        $client = $this->client_service->getClientById($client_id);

        if (is_null($client))
        {
            throw new InvalidClientException
            (
                sprintf
                (
                    "client_id %s does not exists!",
                    $client_id
                )
            );
        }

        if (!$client->isActive() || $client->isLocked())
        {
            throw new LockedClientException
            (
                sprintf
                (
                    'client id %s is locked',
                    $client_id
                )
            );
        }

        if ($client->getApplicationType() != IClient::ApplicationType_Web_App)
        {
            throw new InvalidApplicationType
            (
                  sprintf
                  (
                      "client id %s - Application type must be %s",
                      $client_id,
                      IClient::ApplicationType_Web_App
                  )
            );
        }

        //check redirect uri
        $redirect_uri = $request->getRedirectUri();

        if (!$client->isUriAllowed($redirect_uri))
        {
            throw new UriNotAllowedException
            (
                sprintf
                (
                    "redirect_to %s",
                    $redirect_uri
                )
            );
        }

        //check requested scope
        $scope = $request->getScope();

        if (!$client->isScopeAllowed($scope))
        {
            throw new ScopeNotAllowedException(sprintf("scope %s", $scope));
        }

        $state = $request->getState();
        $nonce = null;

        if($request instanceof OAuth2AuthenticationRequest)
        {
            $nonce = $request->getNonce();
        }

        $authentication_response = $this->auth_service->getUserAuthenticationResponse();

        // user has cancelled login action
        if ($authentication_response == IAuthService::AuthenticationResponse_Cancel)
        {
            //clear saved data ...
            $this->memento_service->forget();
            $this->auth_service->clearUserAuthenticationResponse();
            $this->auth_service->clearUserAuthorizationResponse();

            if($this->shouldPromptLogin($request))
                throw new LoginRequiredException;

            throw new AccessDeniedException;
        }

        //check user logged
        if ($this->mustAuthenticateUser($request))
        {
            if(!$this->canInteractWithEndUser($request))
                throw new LoginRequiredException;

            $this->memento_service->serialize($request->getMessage()->createMemento());
            return $this->auth_strategy->doLogin($request);
        }

        $approval_prompt = $request->getApprovalPrompt();
        $access_type     = $request->getAccessType();
        $user            = $this->auth_service->getCurrentUser();

        if (is_null($user))
        {
            throw new OAuth2GenericException("Invalid Current User");
        }

        $authorization_response = $this->auth_service->getUserAuthorizationResponse();
        //check for former user consents
        $former_user_consent    = $this->user_consent_service->get
        (
            $user->getId(),
            $client->getId(),
            $scope
        );

        $auto_approval          = $approval_prompt == OAuth2Protocol::OAuth2Protocol_Approval_Prompt_Auto;
        $has_former_consent     = !is_null($former_user_consent);

        if ($this->shouldPromptConsent($request) || !($has_former_consent && $auto_approval))
        {
            if( $authorization_response == IAuthService::AuthorizationResponse_None )
            {
                if(!$this->canInteractWithEndUser($request))
                    throw new InteractionRequiredException;

                $this->memento_service->serialize($request->getMessage()->createMemento());
                return $this->auth_strategy->doConsent($request);
            }
            else
            {
                if ($authorization_response == IAuthService::AuthorizationResponse_DenyOnce)
                {
                    if($this->shouldPromptConsent($request))
                        throw new ConsentRequiredException;

                    throw new AccessDeniedException;
                }
            }
            //save possitive consent
            if (is_null($former_user_consent))
            {
                $this->user_consent_service->add($user->getId(), $client->getId(), $scope);
            }
        }
        // build current audience ...
        $audience  = $this->scope_service->getStrAudienceByScopeNames(explode(' ', $scope));

        $auth_code = $this->token_service->createAuthorizationCode
        (
                        $user->getId(),
                        $client_id,
                        $scope,
                        $audience,
                        $redirect_uri,
                        $access_type,
                        $approval_prompt,
                        !is_null($former_user_consent),
                        $state,
                        $nonce
        );

        if (is_null($auth_code))
        {
            throw new OAuth2GenericException("Invalid Auth Code");
        }
        // clear save data ...
        $this->auth_service->clearUserAuthorizationResponse();
        $this->memento_service->forget();

        return new OAuth2AuthorizationResponse($redirect_uri, $auth_code->getValue(), $scope, $state);

    }


    /**
     * @param OAuth2AuthorizationRequest $request
     * @return bool
     */
    private function canInteractWithEndUser(OAuth2AuthorizationRequest $request)
    {
        if($request instanceof OAuth2AuthenticationRequest && $request->getPrompt() === OAuth2Protocol::OAuth2Protocol_Prompt_None)
        {
            return false;
        }
        return true;
    }

    /**
     * @param OAuth2AuthorizationRequest $request
     * @return bool
     */
    private function shouldPromptLogin(OAuth2AuthorizationRequest $request)
    {
        if($request instanceof OAuth2AuthenticationRequest && $request->getPrompt() === OAuth2Protocol::OAuth2Protocol_Prompt_Login)
        {
            return true;
        }
        return false;
    }

    private function shouldPromptConsent(OAuth2AuthorizationRequest $request)
    {
        if($request instanceof OAuth2AuthenticationRequest && $request->getPrompt() === OAuth2Protocol::OAuth2Protocol_Prompt_Consent)
        {
            $this->auth_service->logout();
            return true;
        }
        return false;
    }


    private function shouldForceReLogin(OAuth2AuthorizationRequest $request)
    {
        $now      = time();
        $auth_time = $this->auth_service->getAuthTime();
        if
        (
            $request instanceof OAuth2AuthenticationRequest &&
            $request->getMaxAge() > 0 &&
            !is_null($auth_time) &&
            ($now - $auth_time) > $request->getMaxAge()
        )
        {
            return true;
        }
        return false;
    }

    /**
     * @param OAuth2AuthorizationRequest $request
     * @return bool
     * @throws LoginRequiredException
     */
    private function mustAuthenticateUser(OAuth2AuthorizationRequest $request)
    {

        if($this->shouldPromptLogin($request))
        {
            $this->auth_service->logout();
            return true;
        }

        if($this->shouldForceReLogin($request))
        {
            $this->auth_service->logout();
            return true;
        }

        if (!$this->auth_service->isUserLogged())
            return true;

        return false;
    }

    /**
     * @return mixed|string
     */
    public function getResponseType()
    {
        return OAuth2Protocol::OAuth2Protocol_ResponseType_Code;
    }

    /**
     * Implements last request processing for Authorization code (Access Token Request processing)
     * http://tools.ietf.org/html/rfc6749#section-4.1.3 and
     * http://tools.ietf.org/html/rfc6749#section-4.1.4
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

        if (!($request instanceof OAuth2AccessTokenRequestAuthCode))
        {
            throw new InvalidOAuth2Request;
        }

        try
        {
            parent::completeFlow($request);

            //only confidential clients could use this grant type

            if ($this->current_client->getApplicationType() != IClient::ApplicationType_Web_App)
            {
                throw new InvalidApplicationType
                (
                    sprintf
                    (
                        "client id %s - Application type must be %s",
                        $this->client_auth_context->getId(),
                        IClient::ApplicationType_Web_App
                    )
                );
            }

            $current_redirect_uri = $request->getRedirectUri();
            //verify redirect uri
            if (!$this->current_client->isUriAllowed($current_redirect_uri))
            {
                throw new UriNotAllowedException
                (
                    sprintf
                    (
                        'redirect url %s is not allowed for cliend id %s',
                        $current_redirect_uri,
                        $this->client_auth_context->getId()
                    )
                );
            }

            $code = $request->getCode();
            // verify that the authorization code is valid
            // The client MUST NOT use the authorization code
            // more than once.  If an authorization code is used more than
            // once, the authorization server MUST deny the request and SHOULD
            // revoke (when possible) all tokens previously issued based on
            // that authorization code.  The authorization code is bound to
            // the client identifier and redirection URI.
            $auth_code = $this->token_service->getAuthorizationCode($code);

            $client_id = $auth_code->getClientId();

            //ensure that the authorization code was issued to the authenticated
            //confidential client, or if the client is public, ensure that the
            //code was issued to "client_id" in the request
            if ($client_id != $this->client_auth_context->getId())
            {
                throw new InvalidRedeemAuthCodeException
                (
                    sprintf
                    (
                        "auth code was issued for another client id!."
                    )
                );
            }

            // ensure that the "redirect_uri" parameter is present if the
            // "redirect_uri" parameter was included in the initial authorization
            // and if included ensure that their values are identical.
            $redirect_uri = $auth_code->getRedirectUri();
            if (!empty($redirect_uri) && $redirect_uri !== $current_redirect_uri)
            {
                throw new UriNotAllowedException();
            }

            $access_token  = $this->token_service->createAccessToken($auth_code, $current_redirect_uri);
            $refresh_token = $access_token->getRefreshToken();

            if($this->authCodewasIssuedForOIDC($auth_code))
            {
                $id_token = $this->token_service->createIdToken
                (
                    $auth_code,
                    $access_token
                );

                $response = new OAuth2IdTokenResponse
                (
                    $access_token->getValue(),
                    $access_token->getLifetime(),
                    $id_token->toCompactSerialization(),
                    !is_null($refresh_token) ?
                        $refresh_token->getValue() :
                        null
                );
            }
            else
            {
                $response = new OAuth2AccessTokenResponse
                (
                    $access_token->getValue(),
                    $access_token->getLifetime(),
                    !is_null($refresh_token) ?
                        $refresh_token->getValue() :
                        null
                );
            }

            return $response;

        }
        catch (InvalidAuthorizationCodeException $ex)
        {
            $this->log_service->error($ex);

            throw new InvalidRedeemAuthCodeException
            (
                $ex->getMessage()
            );
        }
    }

    private function authCodewasIssuedForOIDC(AuthorizationCode $auth_code)
    {
        return str_contains($auth_code->getScope(), OAuth2Protocol::OpenIdConnect_Scope);
    }

    /**
     * @param OAuth2Request $request
     * @return mixed|null|OAuth2AccessTokenRequestAuthCode
     */
    public function buildTokenRequest(OAuth2Request $request)
    {
        if ($request instanceof OAuth2TokenRequest)
        {
            if ($request->getGrantType() !== $this->getType())
            {
                return null;
            }
            return new OAuth2AccessTokenRequestAuthCode($request->getMessage());
        }
        return null;
    }
}