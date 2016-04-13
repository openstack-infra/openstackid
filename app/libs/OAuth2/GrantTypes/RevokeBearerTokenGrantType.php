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

use Exception;
use OAuth2\Exceptions\BearerTokenDisclosureAttemptException;
use OAuth2\Exceptions\ExpiredAccessTokenException;
use OAuth2\Exceptions\InvalidClientCredentials;
use OAuth2\Exceptions\InvalidClientException;
use OAuth2\Exceptions\InvalidGrantTypeException;
use OAuth2\Exceptions\InvalidOAuth2Request;
use OAuth2\Exceptions\LockedClientException;
use OAuth2\Exceptions\MissingClientIdParam;
use OAuth2\Exceptions\UnAuthorizedClientException;
use OAuth2\Repositories\IClientRepository;
use OAuth2\Services\ITokenService;
use OAuth2\OAuth2Protocol;
use OAuth2\Requests\OAuth2Request;
use OAuth2\Requests\OAuth2TokenRevocationRequest;
use OAuth2\Responses\OAuth2Response;
use OAuth2\Responses\OAuth2TokenRevocationResponse;
use OAuth2\Services\IClientService;
use Utils\Services\ILogService;

/**
 * Class RevokeTokenGrantType
 * @see http://tools.ietf.org/html/rfc7009
 * The OAuth 2.0 core specification [RFC6749] defines several ways for a
 * client to obtain refresh and access tokens.  This specification
 * supplements the core specification with a mechanism to revoke both
 * types of tokens.  A token is a string representing an authorization
 * grant issued by the resource owner to the client.  A revocation
 * request will invalidate the actual token and, if applicable, other
 * tokens based on the same authorization grant and the authorization
 * grant itself.
 * From an end-user's perspective, OAuth is often used to log into a
 * certain site or application.  This revocation mechanism allows a
 * client to invalidate its tokens if the end-user logs out, changes
 * identity, or uninstalls the respective application.  Notifying the
 * authorization server that the token is no longer needed allows the
 * authorization server to clean up data associated with that token
 * (e.g., session data) and the underlying authorization grant.  This
 * behavior prevents a situation in which there is still a valid
 * authorization grant for a particular client of which the end-user is
 * not aware.  This way, token revocation prevents abuse of abandoned
 * tokens and facilitates a better end-user experience since invalidated
 * authorization grants will no longer turn up in a list of
 * authorization grants the authorization server might present to the
 * end-user.
 * @package oauth2\grant_types
 */
class RevokeBearerTokenGrantType extends AbstractGrantType
{

    const OAuth2Protocol_GrantType_Extension_RevokeToken = 'urn:tools.ietf.org:oauth2:grant_type:revoke_bearer';

    public function __construct(IClientService $client_service, IClientRepository $client_repository, ITokenService $token_service, ILogService $log_service)
    {
        parent::__construct($client_service, $client_repository, $token_service, $log_service);
    }

    /** Given an OAuth2Request, returns true if it can handle it, false otherwise
     * @param OAuth2Request $request
     * @return boolean
     */
    public function canHandle(OAuth2Request $request)
    {
        return $request instanceof OAuth2TokenRevocationRequest && $request->isValid();
    }

    /** defines entry point for first request processing
     * @param OAuth2Request $request
     * @throws InvalidOAuth2Request
     * @return OAuth2Response
     */
    public function handle(OAuth2Request $request)
    {
        throw new InvalidOAuth2Request('not implemented!');
    }

    /**
     * @param OAuth2Request $request
     * @return OAuth2Response
     * @throws BearerTokenDisclosureAttemptException
     * @throws Exception
     * @throws ExpiredAccessTokenException
     * @throws InvalidOAuth2Request
     * @throws UnAuthorizedClientException
     * @throws InvalidClientCredentials
     * @throws InvalidClientException
     * @throws LockedClientException
     * @throws MissingClientIdParam
     */
    public function completeFlow(OAuth2Request $request)
    {

        if (!($request instanceof OAuth2TokenRevocationRequest))
        {
            throw new InvalidOAuth2Request;
        }

        parent::completeFlow($request);

        $token_value = $request->getToken();
        $token_hint  = $request->getTokenHint();

        try
        {
            if (!is_null($token_hint) && !empty($token_hint))
            {
                //we have been provided with a token hint...
                switch ($token_hint)
                {
                    case OAuth2Protocol::OAuth2Protocol_AccessToken:
                    {
                        //check ownership
                        $access_token = $this->token_service->getAccessToken($token_value);

                        if (is_null($access_token))
                        {
                            throw new ExpiredAccessTokenException(sprintf('Access token %s is expired!', $token_value));
                        }

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

                        $this->token_service->revokeAccessToken($token_value, false);
                    }
                    break;
                    case OAuth2Protocol::OAuth2Protocol_RefreshToken:
                    {
                        //check ownership
                        $refresh_token = $this->token_service->getRefreshToken($token_value);

                        if ($refresh_token->getClientId() !== $this->client_auth_context->getId())
                        {
                            throw new BearerTokenDisclosureAttemptException
                            (
                                sprintf
                                (
                                    'refresh token %s does not belongs to client id %s',
                                    $token_value,
                                    $this->client_auth_context->getId()
                                )
                            );
                        }

                        $this->token_service->revokeRefreshToken($token_value, false);
                    }
                    break;
                }
            }
            else
            {
                /*
                 * no token hint given :(
                 * if the server is unable to locate the token using
                 * the given hint, it MUST extend its search across all of its
                 * supported token types.
                 */

                //check and handle access token first ..
                try {
                    //check ownership
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

                    $this->token_service->revokeAccessToken($token_value, false);

                }
                catch (UnAuthorizedClientException $ex1)
                {
                    $this->log_service->error($ex1);
                    throw $ex1;
                }
                catch (Exception $ex)
                {
                    $this->log_service->warning($ex);
                    //access token was not found, check refresh token
                    //check ownership
                    $refresh_token = $this->token_service->getRefreshToken($token_value);

                    if ($refresh_token->getClientId() !== $this->client_auth_context->getId())
                    {
                        throw new BearerTokenDisclosureAttemptException
                        (
                            sprintf
                            (
                                'refresh token %s does not belongs to client id %s',
                                $token_value,
                                $this->client_auth_context->getId()
                            )
                        );
                    }
                    $this->token_service->revokeRefreshToken($token_value, false);
                }
            }

            return new OAuth2TokenRevocationResponse;
        }
        catch (InvalidGrantTypeException $ex)
        {
            throw new BearerTokenDisclosureAttemptException
            (
                $ex->getMessage()
            );
        }
    }

    /**
     * get grant type
     * @return string
     */
    public function getType()
    {
        return self::OAuth2Protocol_GrantType_Extension_RevokeToken;
    }

    /** get grant type response type
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
    public function buildTokenRequest(OAuth2Request $request)
    {
        throw new InvalidOAuth2Request('not implemented!');
    }
}