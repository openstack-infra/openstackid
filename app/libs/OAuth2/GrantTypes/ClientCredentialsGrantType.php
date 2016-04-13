<?php namespace OAuth2\GrantTypes;

/**
 * Copyright 2016 OpenStack Foundation
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
use OAuth2\Exceptions\InvalidGrantTypeException;
use OAuth2\Exceptions\InvalidOAuth2Request;
use OAuth2\Exceptions\ScopeNotAllowedException;
use OAuth2\Models\IClient;
use OAuth2\OAuth2Protocol;
use OAuth2\Requests\OAuth2AccessTokenRequestClientCredentials;
use OAuth2\Requests\OAuth2Request;
use OAuth2\Requests\OAuth2TokenRequest;
use OAuth2\Responses\OAuth2AccessTokenResponse;
use OAuth2\Responses\OAuth2Response;
use OAuth2\Services\IApiScopeService;
use OAuth2\Services\IClientService;
use OAuth2\Services\ITokenService;
use Utils\Services\ILogService;

/**
 * Class ClientCredentialsGrantType
 * The client can request an access token using only its client
 * credentials (or other supported means of authentication) when the
 * client is requesting access to the protected resources under its
 * control, or those of another resource owner that have been previously
 * arranged with the authorization server (the method of which is beyond
 * the scope of this specification).
 * @see http://tools.ietf.org/html/rfc6749#section-4.4
 * @package OAuth2\GrantTypes
 */
class ClientCredentialsGrantType extends AbstractGrantType
{

    /**
     * @var IApiScopeService
     */
    private $scope_service;

    /**
     * @param IApiScopeService $scope_service
     * @param IClientService $client_service
     * @param ITokenService $token_service
     * @param ILogService $log_service
     */
    public function __construct
    (
        IApiScopeService $scope_service,
        IClientService   $client_service,
        ITokenService    $token_service,
        ILogService      $log_service
    )
    {
        parent::__construct($client_service, $token_service, $log_service);

        $this->scope_service = $scope_service;
    }

    /** Given an OAuth2Request, returns true if it can handle it, false otherwise
     * @param OAuth2Request $request
     * @return boolean
     */
    public function canHandle(OAuth2Request $request)
    {
        return $request instanceof OAuth2TokenRequest && $request->isValid() && $request->getGrantType() == $this->getType();
    }

    /**
     * get grant type response type
     * @return array
     * @throws InvalidOAuth2Request
     */
    public function getResponseType()
    {
        throw new InvalidOAuth2Request('not implemented!');
    }

    /**
     * @param OAuth2Request $request
     * @return OAuth2Response
     * @throws InvalidOAuth2Request
     */
    public function handle(OAuth2Request $request)
    {
        throw new InvalidOAuth2Request('not implemented!');
    }

    /**
     * @param OAuth2Request $request
     * @return OAuth2Response
     * @throws ScopeNotAllowedException
     * @throws InvalidOAuth2Request
     * @throws InvalidApplicationType
     * @throws InvalidGrantTypeException
     */
    public function completeFlow(OAuth2Request $request)
    {
        if (!($request instanceof OAuth2AccessTokenRequestClientCredentials)) {
            throw new InvalidOAuth2Request;
        }

        if ($request->getGrantType() != $this->getType()) {
            throw new InvalidGrantTypeException;
        }

        parent::completeFlow($request);

        //only confidential clients could use this grant type
        if ($this->current_client->getApplicationType() != IClient::ApplicationType_Service) {
            throw new InvalidApplicationType
            (
                sprintf
                (
                    'client id %s client type must be %s',
                    $this->client_auth_context->getId(),
                    IClient::ApplicationType_Service
                )
            );
        }

        //check requested scope
        $scope = $request->getScope();
        if (is_null($scope) || empty($scope) || !$this->current_client->isScopeAllowed($scope)) {
            throw new ScopeNotAllowedException(sprintf("scope %s", $scope));
        }

        // build current audience ...
        $audience = $this->scope_service->getStrAudienceByScopeNames(explode(' ', $scope));

        //build access token
        $access_token = $this->token_service->createAccessTokenFromParams($this->client_auth_context->getId(), $scope, $audience);

        $response = new OAuth2AccessTokenResponse($access_token->getValue(), $access_token->getLifetime(), null);

        return $response;

    }

    /** builds specific Token request
     * @param OAuth2Request $request
     * @return mixed
     */
    public function buildTokenRequest(OAuth2Request $request)
    {

        if ($request instanceof OAuth2TokenRequest)
        {
            if ($request->getGrantType() !== $this->getType())
            {
                return null;
            }
            return new OAuth2AccessTokenRequestClientCredentials($request->getMessage());
        }

        return null;
    }

    /**
     * get grant type
     * @return string
     */
    public function getType()
    {
        return OAuth2Protocol::OAuth2Protocol_GrantType_ClientCredentials;
    }
}