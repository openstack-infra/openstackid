<?php namespace OAuth2\GrantTypes;

/**
 * Copyright 2015 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

use OAuth2\Exceptions\InvalidApplicationType;
use OAuth2\Exceptions\InvalidClientType;
use OAuth2\Exceptions\InvalidOAuth2Request;
use OAuth2\Exceptions\OAuth2GenericException;
use OAuth2\Models\IClient;
use OAuth2\Repositories\IClientRepository;
use OAuth2\Services\ITokenService;
use OAuth2\OAuth2Protocol;
use OAuth2\Repositories\IServerPrivateKeyRepository;
use OAuth2\Requests\OAuth2AuthenticationRequest;
use OAuth2\Requests\OAuth2AuthorizationRequest;
use OAuth2\Requests\OAuth2Request;
use OAuth2\Responses\OAuth2HybridTokenFragmentResponse;
use OAuth2\Responses\OAuth2Response;
use OAuth2\Services\IApiScopeService;
use OAuth2\Services\IClientJWKSetReader;
use OAuth2\Services\IClientService;
use OAuth2\Services\IMementoOAuth2SerializerService;
use OAuth2\Services\IPrincipalService;
use OAuth2\Services\ISecurityContextService;
use OAuth2\Services\IUserConsentService;
use OAuth2\Strategies\IOAuth2AuthenticationStrategy;
use Utils\Services\IAuthService;
use Utils\Services\ILogService;

/**
 * Class HybridGrantType
 * @package OAuth2\GrantTypes
 */
class HybridGrantType extends InteractiveGrantType
{

    /**
     * HybridGrantType constructor.
     * @param IApiScopeService $scope_service
     * @param IClientService $client_service
     * @param IClientRepository $client_repository
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
        IClientRepository               $client_repository,
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
            $client_repository,
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
                    OAuth2Protocol::OAuth2Protocol_GrantType_Hybrid
                )
            );
    }

    /**
     * get grant type
     * @return string
     */
    public function getType()
    {
        return OAuth2Protocol::OAuth2Protocol_GrantType_Hybrid;
    }

    /**
     * get grant type response type
     * @return array
     */
    public function getResponseType()
    {
        return OAuth2Protocol::getValidResponseTypes(OAuth2Protocol::OAuth2Protocol_GrantType_Hybrid);
    }

    /**
     * @param OAuth2Request $request
     * @return OAuth2Response
     * @throws InvalidOAuth2Request
     */
    public function buildTokenRequest(OAuth2Request $request)
    {
        throw new InvalidOAuth2Request('not implemented!');
    }

    /**
     * @param OAuth2AuthorizationRequest $request
     * @param bool $has_former_consent
     * @return OAuth2Response
     * @throws InvalidOAuth2Request
     * @throws OAuth2GenericException
     */
    protected function buildResponse(OAuth2AuthorizationRequest $request, $has_former_consent)
    {
        if (!($request instanceof OAuth2AuthenticationRequest)) {
            throw new InvalidOAuth2Request;
        }

        $user = $this->auth_service->getCurrentUser();

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
            $request->getNonce(),
            $request->getResponseType(),
            $request->getPrompt(true)
        );

        if (is_null($auth_code)) {
            throw new OAuth2GenericException("Invalid Auth Code");
        }

        $access_token = null;
        $id_token = null;


        if (in_array(OAuth2Protocol::OAuth2Protocol_ResponseType_Token, $request->getResponseType(false)))
        {
            $access_token = $this->token_service->createAccessToken
            (
                $auth_code,
                $request->getRedirectUri()
            );
        }

        if (in_array(OAuth2Protocol::OAuth2Protocol_ResponseType_IdToken, $request->getResponseType(false)))
        {

            $id_token = $this->token_service->createIdToken
            (
                $request->getNonce(),
                $request->getClientId(),
                $access_token,
                $auth_code
            );
        }

        return new OAuth2HybridTokenFragmentResponse
        (
            $request->getRedirectUri(),
            $auth_code->getValue(),
            is_null($access_token) ? null : $access_token->getValue(),
            is_null($access_token) ? null : $access_token->getLifetime(),
            is_null($access_token) ? null : $request->getScope(),
            $request->getState(),
            $session_state,
            is_null($id_token) ? null : $id_token->toCompactSerialization()
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
        if
        (
        !(
            $client->getClientType()      === IClient::ClientType_Confidential ||
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
                    IClient::ClientType_Confidential,
                    IClient::ApplicationType_Native
                )
            );
        }
    }
}