<?php

namespace oauth2\grant_types;

use oauth2\exceptions\AccessDeniedException;
use oauth2\exceptions\InvalidApplicationType;
use oauth2\exceptions\InvalidClientException;
use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\exceptions\LockedClientException;
use oauth2\exceptions\OAuth2GenericException;
use oauth2\exceptions\ScopeNotAllowedException;
use oauth2\exceptions\UnsupportedResponseTypeException;
use oauth2\exceptions\UriNotAllowedException;
use oauth2\models\IClient;
use oauth2\OAuth2Protocol;
use oauth2\requests\OAuth2AuthorizationRequest;
use oauth2\requests\OAuth2Request;
use oauth2\responses\OAuth2AccessTokenFragmentResponse;
use oauth2\services\IApiScopeService;
use oauth2\services\IClientService;
use oauth2\services\IMementoOAuth2SerializerService;
use oauth2\services\ITokenService;
use oauth2\services\IUserConsentService;
use oauth2\strategies\IOAuth2AuthenticationStrategy;
use utils\services\IAuthService;
use utils\services\ILogService;

/**
 * Class ImplicitGrantType
 * http://tools.ietf.org/html/rfc6749#section-4.2
 * The implicit grant type is used to obtain access tokens (it does not
 * support the issuance of refresh tokens) and is optimized for public
 * clients known to operate a particular redirection URI.  These clients
 * are typically implemented in a browser using a scripting language
 * such as JavaScript.
 * Since this is a redirection-based flow, the client must be capable of
 * interacting with the resource owner's user-agent (typically a web
 * browser) and capable of receiving incoming requests (via redirection)
 * from the authorization server.
 * Unlike the authorization code grant type, in which the client makes
 * separate requests for authorization and for an access token, the
 * client receives the access token as the result of the authorization
 * request.
 * The implicit grant type does not include client authentication, and
 * relies on the presence of the resource owner and the registration of
 * the redirection URI.  Because the access token is encoded into the
 * redirection URI, it may be exposed to the resource owner and other
 * applications residing on the same device.
 * @package oauth2\grant_types
 */
class ImplicitGrantType extends AbstractGrantType
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
     * @var IMementoOAuth2SerializerService
     */
    private $memento_service;

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
    public function __construct(
        IApiScopeService $scope_service,
        IClientService $client_service,
        ITokenService $token_service,
        IAuthService $auth_service,
        IOAuth2AuthenticationStrategy $auth_strategy,
        ILogService $log_service,
        IUserConsentService $user_consent_service,
        IMementoOAuth2SerializerService $memento_service
    ) {
        parent::__construct($client_service, $token_service, $log_service);

        $this->user_consent_service = $user_consent_service;
        $this->scope_service = $scope_service;
        $this->auth_service = $auth_service;
        $this->auth_strategy = $auth_strategy;
        $this->memento_service = $memento_service;
    }

    /** Given an OAuth2Request, returns true if it can handle it, false otherwise
     * @param OAuth2Request $request
     * @return boolean
     */
    public function canHandle(OAuth2Request $request)
    {
        return ($request instanceof OAuth2AuthorizationRequest && $request->isValid() && $request->getResponseType() == $this->getResponseType());
    }

    /** get grant type response type
     * @return mixed
     */
    public function getResponseType()
    {
        return OAuth2Protocol::OAuth2Protocol_ResponseType_Token;
    }

    /**
     * @param OAuth2Request $request
     * @return mixed|OAuth2AccessTokenFragmentResponse
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
        if (!($request instanceof OAuth2AuthorizationRequest)) {
            throw new InvalidOAuth2Request;
        }

        $client_id = $request->getClientId();

        $response_type = $request->getResponseType();

        if ($response_type !== $this->getResponseType()) {
            throw new UnsupportedResponseTypeException(sprintf("response_type %s", $response_type));
        }

        $client = $this->client_service->getClientById($client_id);

        if (is_null($client)) {
            throw new InvalidClientException($client_id, sprintf("client_id %s", $client_id));
        }

        if (!$client->isActive() || $client->isLocked()) {
            throw new LockedClientException($client, sprintf('client id %s', $client));
        }

        //check client type
        // only public clients could use this grant type
        if ($client->getApplicationType() != IClient::ApplicationType_JS_Client) {
            throw new InvalidApplicationType($client_id,
                sprintf('client id %s client type must be JS CLIENT', $client_id));
        }

        //check redirect uri
        $redirect_uri = $request->getRedirectUri();
        if (!$client->isUriAllowed($redirect_uri)) {
            throw new UriNotAllowedException(sprintf("redirect_to %s", $redirect_uri));
        }

        //check requested scope
        $scope = $request->getScope();

        if (is_null($scope) || empty($scope) || !$client->isScopeAllowed($scope)) {
            throw new ScopeNotAllowedException(sprintf("scope %s", $scope));
        }

        $state = $request->getState();
        //check user logged

        $authentication_response = $this->auth_service->getUserAuthenticationResponse();

        if ($authentication_response == IAuthService::AuthenticationResponse_Cancel) {
            //clear saved data ...
            $this->memento_service->forget();
            $this->auth_service->clearUserAuthenticationResponse();
            $this->auth_service->clearUserAuthorizationResponse();
            throw new AccessDeniedException;
        }

        if (!$this->auth_service->isUserLogged()) {
            $this->memento_service->serialize($request->getMessage()->createMemento());

            return $this->auth_strategy->doLogin($request);
        }

        $approval_prompt = $request->getApprovalPrompt();

        $user = $this->auth_service->getCurrentUser();

        if (is_null($user)) {
            throw new OAuth2GenericException("Invalid Current User");
        }
        //validate authorization
        //check for former user consents
        $authorization_response = $this->auth_service->getUserAuthorizationResponse();
        $former_user_consent = $this->user_consent_service->get($user->getId(), $client->getId(), $scope);
        if (!(!is_null($former_user_consent) && $approval_prompt == OAuth2Protocol::OAuth2Protocol_Approval_Prompt_Auto)) {
            if ($authorization_response == IAuthService::AuthorizationResponse_None) {
                $this->memento_service->serialize($request->getMessage()->createMemento());

                return $this->auth_strategy->doConsent($request);
            } else {
                if ($authorization_response == IAuthService::AuthorizationResponse_DenyOnce) {
                    //clear saved data ...
                    $this->memento_service->forget();
                    $this->auth_service->clearUserAuthorizationResponse();
                    throw new AccessDeniedException;
                }
            }
            //save possitive consent
            if (is_null($former_user_consent)) {
                $this->user_consent_service->add($user->getId(), $client->getId(), $scope);
            }
        }


        // build current audience ...
        $audience = $this->scope_service->getStrAudienceByScopeNames(explode(' ', $scope));
        //build access token
        $access_token = $this->token_service->createAccessTokenFromParams($client_id, $scope, $audience,
            $user->getId());
        //clear saved data ...
        $this->memento_service->forget();
        $this->auth_service->clearUserAuthorizationResponse();

        return new OAuth2AccessTokenFragmentResponse($redirect_uri, $access_token->getValue(),
            $access_token->getLifetime(), $scope, $state);

    }

    public function completeFlow(OAuth2Request $request)
    {
        throw new InvalidOAuth2Request('not implemented!');
    }

    /**
     * get grant type
     * @return mixed
     */
    public function getType()
    {
        return OAuth2Protocol::OAuth2Protocol_GrantType_Implicit;
    }

    /**
     * @param OAuth2Request $request
     * @return mixed|void
     * @throws \oauth2\exceptions\InvalidOAuth2Request
     */
    public function buildTokenRequest(OAuth2Request $request)
    {
        throw new InvalidOAuth2Request('not implemented!');
    }
}