<?php

namespace oauth2\grant_types;

use oauth2\exceptions\InvalidApplicationType;
use oauth2\exceptions\InvalidClientType;
use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\factories\OAuth2AccessTokenFragmentResponseFactory;
use oauth2\models\IClient;
use oauth2\OAuth2Protocol;
use oauth2\repositories\IServerPrivateKeyRepository;
use oauth2\requests\OAuth2AuthorizationRequest;
use oauth2\requests\OAuth2Request;
use oauth2\responses\OAuth2AccessTokenFragmentResponse;
use oauth2\services\IApiScopeService;
use oauth2\services\IClientJWKSetReader;
use oauth2\services\IClientService;
use oauth2\services\IMementoOAuth2SerializerService;
use oauth2\services\IPrincipalService;
use oauth2\services\ISecurityContextService;
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
class ImplicitGrantType extends InteractiveGrantType
{

    /***
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
     * @param IServerPrivateKeyRepository $server_private_key_repository
     * @param IClientJWKSetReader $jwk_set_reader_service
     */
    public function __construct
    (
        IApiScopeService                $scope_service,
        IClientService                  $client_service,
        ITokenService                   $token_service,
        IAuthService                    $auth_service,
        IOAuth2AuthenticationStrategy   $auth_strategy,
        ILogService                     $log_service,
        IUserConsentService             $user_consent_service,
        IMementoOAuth2SerializerService $memento_service,
        ISecurityContextService         $security_context_service,
        IPrincipalService               $principal_service,
        IServerPrivateKeyRepository     $server_private_key_repository,
        IClientJWKSetReader             $jwk_set_reader_service
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
            $memento_service,
            $server_private_key_repository,
            $jwk_set_reader_service
        );
    }

    /** Given an OAuth2Request, returns true if it can handle it, false otherwise
     * @param OAuth2Request $request
     * @return boolean
     */
    public function canHandle(OAuth2Request $request)
    {
        return
        (
            $request instanceof OAuth2AuthorizationRequest &&
            $request->isValid() &&
            OAuth2Protocol::responseTypeBelongsToFlow
            (
                $request->getResponseType(false),
                OAuth2Protocol::OAuth2Protocol_GrantType_Implicit
            )
        );
    }

    /**
     * get grant type response type
     * OAuth 2.0 Response Type value that determines the authorization processing flow to be used, including what
     * parameters are returned from the endpoints used. When using the Implicit Flow, this value is id_token token or
     * id_token. The meanings of both of these values are defined in OAuth 2.0 Multiple Response Type Encoding Practices
     * [OAuth.Responses]. No Access Token is returned when the value is id_token.
     * NOTE: While OAuth 2.0 also defines the token Response Type value for the Implicit Flow, OpenID Connect does not
     * use this Response Type, since no ID Token would be returned.
     * @return array
     */
    public function getResponseType()
    {
        return OAuth2Protocol::getValidResponseTypes(OAuth2Protocol::OAuth2Protocol_GrantType_Implicit);
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

    /**
     * @param OAuth2AuthorizationRequest $request
     * @param bool $has_former_consent
     * @return OAuth2AccessTokenFragmentResponse
     */
    protected function buildResponse(OAuth2AuthorizationRequest $request, $has_former_consent)
    {
        // build current audience ...
        $audience = $this->scope_service->getStrAudienceByScopeNames
        (
            explode
            (
                OAuth2Protocol::OAuth2Protocol_Scope_Delimiter,
                $request->getScope()
            )
        );

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

        return OAuth2AccessTokenFragmentResponseFactory::build
        (
            $request,
            $audience,
            $session_state,
            $this->auth_service->getCurrentUser(),
            $this->token_service
        );
    }

    /**
     * @param IClient $client
     * @throws InvalidApplicationType
     * @throws InvalidClientType
     * @return void
     */
    protected function checkClientTypeAccess(IClient $client)
    {
        //check client type
        // only public clients could use this grant type
        if( $client->getClientType() != IClient::ClientType_Public )
        {
            throw new InvalidClientType
            (
                sprintf
                (
                    'client id %s client type must be %s',
                    $client->getClientId(),
                    IClient::ClientType_Public
                )
            );
        }
    }
}