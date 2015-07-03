<?php

namespace oauth2\grant_types;

use Exception;
use oauth2\exceptions\AccessDeniedException;
use oauth2\exceptions\InvalidApplicationType;
use oauth2\exceptions\InvalidAuthorizationCodeException;
use oauth2\exceptions\InvalidClientException;
use oauth2\exceptions\InvalidClientType;
use oauth2\exceptions\InvalidLoginHint;
use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\exceptions\InvalidRedeemAuthCodeException;
use oauth2\exceptions\LockedClientException;
use oauth2\exceptions\OAuth2GenericException;
use oauth2\exceptions\ScopeNotAllowedException;
use oauth2\exceptions\UnsupportedResponseTypeException;
use oauth2\exceptions\UriNotAllowedException;
use oauth2\factories\OAuth2AccessTokenResponseFactory;
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
use oauth2\responses\OAuth2Response;
use oauth2\services\IApiScopeService;
use oauth2\services\IClientService;
use oauth2\services\IMementoOAuth2SerializerService;
use oauth2\services\IPrincipalService;
use oauth2\services\ISecurityContextService;
use oauth2\services\ITokenService;
use oauth2\services\IUserConsentService;
use oauth2\strategies\IOAuth2AuthenticationStrategy;
use openid\model\IOpenIdUser;
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
class AuthorizationCodeGrantType extends InteractiveGrantType
{

    /**
     * @param IApiScopeService $scope_service
     * @param IClientService $client_service
     * @param ITokenService $token_service
     * @param IAuthService $auth_service
     * @param IOAuth2AuthenticationStrategy $auth_strategy
     * @param ILogService $log_service
     * @param IUserConsentService $user_consent_service
     * @param IMementoOAuth2SerializerService $memento_service
     * @param ISecurityContextService $security_context_service
     * @param IPrincipalService $principal_service
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
        IMementoOAuth2SerializerService $memento_service,
        ISecurityContextService $security_context_service,
        IPrincipalService $principal_service
    )
    {

        parent::__construct
        (
            $client_service,
            $token_service,
            $log_service,
            $security_context_service,
            $principal_service,
            $auth_service,
            $user_consent_service,
            $scope_service,
            $auth_strategy,
            $memento_service
        );
    }

    /**
     * @param OAuth2Request $request
     * @return bool
     */
    public function canHandle(OAuth2Request $request)
    {
        if
        (
            $request instanceof OAuth2AuthorizationRequest &&
            $request->isValid() &&
            OAuth2Protocol::responseTypeBelongsToFlow
            (
                $request->getResponseType(false),
                OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode
            )
        )
        {
            return true;
        }
        if
        (
            $request instanceof OAuth2TokenRequest &&
            $request->isValid() &&
            $request->getGrantType() == $this->getType()
        )
        {
            return true;
        }

        return false;
    }

    /**
     * @return mixed|string
     */
    public function getType()
    {
        return OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode;
    }

    /**
     * @return array
     */
    public function getResponseType()
    {
        return OAuth2Protocol::getValidResponseTypes(OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode);
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

            $this->security_context_service->clear();

            return OAuth2AccessTokenResponseFactory::build
            (
                $this->token_service,
                $auth_code,
                $this->token_service->createAccessToken($auth_code, $current_redirect_uri)
            );

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

    /**
     * @param IClient $client
     * @throws InvalidApplicationType
     * @throws InvalidClientType
     * @return void
     */
    protected function checkClientTypeAccess(IClient $client)
    {
        if
        (
           !(
               $client->getApplicationType() === IClient::ApplicationType_Web_App ||
               $client->getApplicationType() === IClient::ApplicationType_Native
           )
        )
        {
            throw new InvalidApplicationType
            (
                sprintf
                (
                    "client id %s - Application type must be %s or %s",
                    $client->getClientId(),
                    IClient::ApplicationType_Web_App,
                    IClient::ApplicationType_Native
                )
            );
        }

    }

    /**
     * @param OAuth2AuthorizationRequest $request
     * @param bool $has_former_consent
     * @return OAuth2AuthorizationResponse
     * @throws OAuth2GenericException
     */
    protected function buildResponse(OAuth2AuthorizationRequest $request, $has_former_consent)
    {
        $user   = $this->auth_service->getCurrentUser();
        $client = $this->client_service->getClientById($request->getClientId());

        // build current audience ...
        $audience = $this->scope_service->getStrAudienceByScopeNames
        (
            explode
            (
                OAuth2Protocol::OAuth2Protocol_Scope_Delimiter,
                $request->getScope()
            )
        );

        $nonce = null;

        if($request instanceof OAuth2AuthenticationRequest)
        {
            $nonce = $request->getNonce();
        }

        $auth_code = $this->token_service->createAuthorizationCode
        (
            $user->getId(),
            $request->getClientId(),
            $request->getScope(),
            $audience,
            $request->getRedirectUri(),
            $request->getAccessType(),
            $request->getApprovalPrompt(),
            $has_former_consent,
            $request->getState(),
            $nonce
        );

        if (is_null($auth_code))
        {
            throw new OAuth2GenericException("Invalid Auth Code");
        }
        // http://openid.net/specs/openid-connect-session-1_0.html#CreatingUpdatingSessions
        $session_state = self::getSessionState
        (
            self::getOrigin
            (
                $request->getRedirectUri()
            ),
            $request->getClientId(),

            $this->principal_service->get()->getOPBrowserState()
        );

        return new OAuth2AuthorizationResponse
        (
            $request->getRedirectUri(),
            $auth_code->getValue(),
            $request->getScope(),
            $request->getState(),
            $session_state
        );
    }


}