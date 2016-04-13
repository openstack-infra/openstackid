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

use OAuth2\Exceptions\BearerTokenDisclosureAttemptException;
use OAuth2\Exceptions\ExpiredAccessTokenException;
use OAuth2\Exceptions\InvalidAccessTokenException;
use OAuth2\Exceptions\InvalidApplicationType;
use OAuth2\Exceptions\InvalidClientCredentials;
use OAuth2\Exceptions\InvalidClientException;
use OAuth2\Exceptions\InvalidGrantTypeException;
use OAuth2\Exceptions\InvalidOAuth2Request;
use OAuth2\Exceptions\LockedClientException;
use OAuth2\Models\IClient;
use OAuth2\Repositories\IClientRepository;
use OAuth2\Requests\OAuth2AccessTokenValidationRequest;
use OAuth2\Requests\OAuth2Request;
use OAuth2\Responses\OAuth2AccessTokenValidationResponse;
use OAuth2\Responses\OAuth2Response;
use OAuth2\Services\IClientService;
use OAuth2\Services\ITokenService;
use Utils\IPHelper;
use Utils\Services\IAuthService;
use Utils\Services\ILogService;

/**
 * Class ValidateBearerTokenGrantType
 * In OAuth2, the contents of tokens are opaque to clients.  This means
 * that the client does not need to know anything about the content or
 * structure of the token itself, if there is any.  However, there is
 * still a large amount of metadata that may be attached to a token,
 * such as its current validity, approved scopes, and extra information
 * about the authentication context in which the token was issued.
 * These pieces of information are often vital to Protected Resources
 * making authorization decisions based on the tokens being presented.
 * Since OAuth2 defines no direct relationship between the Authorization
 * Server and the Protected Resource, only that they must have an
 * agreement on the tokens themselves, there have been many different
 * approaches to bridging this gap.
 * This specification defines an Introspection Endpoint that allows the
 * holder of a token to query the Authorization Server to discover the
 * set of metadata for a token.  A Protected Resource may use the
 * mechanism described in this draft to query the Introspection Endpoint
 * in a particular authorization decision context and ascertain the
 * relevant metadata about the token in order to make this authorization
 * decision appropriately.
 * The endpoint SHOULD also require some form of authentication to
 * access this endpoint, such as the Client Authentication as described
 * in OAuth 2 Core Specification [RFC6749] or a separate OAuth 2.0
 * Access Token.  The methods of managing and validating these
 * authentication credentials are out of scope of this specification.
 * @see http://tools.ietf.org/html/draft-richer-oauth-introspection-04
 * @package oauth2\grant_types
 */
class ValidateBearerTokenGrantType extends AbstractGrantType
{

    const OAuth2Protocol_GrantType_Extension_ValidateBearerToken = 'urn:tools.ietf.org:oauth2:grant_type:validate_bearer';

    /**
     * @var IAuthService
     */
    private $auth_service;

    /**
     * ValidateBearerTokenGrantType constructor.
     * @param IClientService $client_service
     * @param IClientRepository $client_repository
     * @param ITokenService $token_service
     * @param IAuthService $auth_service
     * @param ILogService $log_service
     */
    public function __construct
    (
        IClientService    $client_service,
        IClientRepository $client_repository,
        ITokenService     $token_service,
        IAuthService      $auth_service,
        ILogService       $log_service
    )
    {
        parent::__construct($client_service, $client_repository, $token_service, $log_service);
        $this->auth_service = $auth_service;
    }

    /**
     * @param OAuth2Request $request
     * @return bool
     */
    public function canHandle(OAuth2Request $request)
    {
        return $request instanceof OAuth2AccessTokenValidationRequest && $request->isValid();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return self::OAuth2Protocol_GrantType_Extension_ValidateBearerToken;
    }

    /**
     * @param OAuth2Request $request
     * @return OAuth2Response
     * @throws InvalidOAuth2Request
     */
    public function handle(OAuth2Request $request)
    {
        throw new InvalidOAuth2Request('Not Implemented!');
    }

    /**
     * @param OAuth2Request $request
     * @return OAuth2Response
     * @throws BearerTokenDisclosureAttemptException
     * @throws ExpiredAccessTokenException
     * @throws InvalidApplicationType
     * @throws InvalidOAuth2Request
     * @throws LockedClientException
     * @throws InvalidClientCredentials
     * @throws InvalidClientException
     */
    public function completeFlow(OAuth2Request $request)
    {

        if (!($request instanceof OAuth2AccessTokenValidationRequest)) {
            throw new InvalidOAuth2Request;
        }

        parent::completeFlow($request);

        $token_value = $request->getToken();

        try {

            $access_token = $this->token_service->getAccessToken($token_value);

            if (is_null($access_token))
            {
                throw new ExpiredAccessTokenException
                (
                    sprintf
                    (
                        'Access token %s is expired!',
                        $token_value
                    )
                );
            }

            if (!$this->current_client->isResourceServerClient())
            {
                // if current client is not a resource server, then we could only access to our own tokens
                if ($access_token->getClientId() !== $this->client_auth_context->getId())
                {
                    throw new BearerTokenDisclosureAttemptException
                    (
                        sprintf
                        (
                            'access token %s does not belongs to client id %s',
                            $token_value,
                            $this->client_auth_context->getId()
                        )
                    );
                }
            }
            else
            {
                // current client is a resource server, validate client type (must be confidential)
                if ($this->current_client->getClientType() !== IClient::ClientType_Confidential)
                {
                    throw new InvalidApplicationType
                    (
                        'resource server client is not of confidential type!'
                    );
                }
                //validate resource server IP address
                $current_ip       = IPHelper::getUserIp();
                $resource_server = $this->current_client->getResourceServer();
                //check if resource server is active
                if (!$resource_server->isActive())
                {
                    throw new LockedClientException
                    (
                        'resource server is disabled!'
                    );
                }
                //check resource server ip address
                if (!$resource_server->isOwn($current_ip))
                {
                    throw new BearerTokenDisclosureAttemptException
                    (
                        sprintf
                        (
                            'resource server ip (%s) differs from current request ip %s',
                            $resource_server->getIPAddresses(),
                            $current_ip
                        )
                    );
                }
                // check if current ip belongs to a registered resource server audience
                if (!$this->token_service->checkAccessTokenAudience($access_token, $current_ip))
                {
                    throw new BearerTokenDisclosureAttemptException
                    (
                        sprintf
                        (
                            'access token current audience does not match with current request ip %s',
                            $current_ip
                        )
                    );
                }
            }
            $this->log_service->debug_msg(sprintf("access token client id %s", $access_token->getClientId()));

            $issued_client = $this->client_repository->getClientById($access_token->getClientId());

            if (is_null($issued_client))
            {
                throw new BearerTokenDisclosureAttemptException
                (
                    sprintf
                    (
                        'access token %s does not belongs to client id %s',
                        $token_value,
                        $access_token->getClientId()
                    )
                );
            }

            $user_id = $access_token->getUserId();
            $user    = is_null($user_id) ? null: $this->auth_service->getUserById($user_id);

            return new OAuth2AccessTokenValidationResponse
            (
                $token_value,
                $access_token->getScope(),
                $access_token->getAudience(),
                $issued_client,
                $access_token->getRemainingLifetime(),
                $user,
                $issued_client->getRedirectUris(),
                $issued_client->getClientAllowedOrigins()
            );
        }
        catch (InvalidAccessTokenException $ex1)
        {
            $this->log_service->warning($ex1);
            throw new BearerTokenDisclosureAttemptException($ex1->getMessage());
        }
        catch (InvalidGrantTypeException $ex2)
        {
            $this->log_service->warning($ex2);
            throw new BearerTokenDisclosureAttemptException($ex2->getMessage());
        }
        catch(ExpiredAccessTokenException $ex3)
        {
            $this->log_service->warning($ex3);
            $this->token_service->expireAccessToken($token_value);
            throw $ex3;
        }
    }

    /**
     * @return array
     * @throws InvalidOAuth2Request
     */
    public function getResponseType()
    {
        throw new InvalidOAuth2Request('Not Implemented!');
    }

    /**
     * @param OAuth2Request $request
     * @return OAuth2Response
     * @throws InvalidOAuth2Request
     */
    public function buildTokenRequest(OAuth2Request $request)
    {
        throw new InvalidOAuth2Request('Not Implemented!');
    }
}