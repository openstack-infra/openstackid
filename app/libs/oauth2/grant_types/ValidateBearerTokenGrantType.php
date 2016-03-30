<?php

namespace oauth2\grant_types;

use oauth2\exceptions\BearerTokenDisclosureAttemptException;
use oauth2\exceptions\ExpiredAccessTokenException;
use oauth2\exceptions\InvalidAccessTokenException;
use oauth2\exceptions\InvalidApplicationType;
use oauth2\exceptions\InvalidGrantTypeException;
use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\exceptions\LockedClientException;
use oauth2\models\IClient;
use oauth2\requests\OAuth2AccessTokenValidationRequest;
use oauth2\requests\OAuth2Request;
use oauth2\responses\OAuth2AccessTokenValidationResponse;
use oauth2\services\IClientService;
use oauth2\services\ITokenService;
use utils\IPHelper;
use utils\services\IAuthService;
use utils\services\ILogService;

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
 * http://tools.ietf.org/html/draft-richer-oauth-introspection-04
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
     * @param IClientService $client_service
     * @param ITokenService $token_service
     * @param IAuthService $auth_service
     * @param ILogService $log_service
     */
    public function __construct
    (
        IClientService $client_service,
        ITokenService $token_service,
        IAuthService  $auth_service,
        ILogService   $log_service
    )
    {
        parent::__construct($client_service, $token_service, $log_service);
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
     * @return mixed|void
     * @throws \oauth2\exceptions\InvalidOAuth2Request
     */
    public function handle(OAuth2Request $request)
    {
        throw new InvalidOAuth2Request('Not Implemented!');
    }

    /**
     * @param OAuth2Request $request
     * @return OAuth2AccessTokenValidationResponse
     * @throws BearerTokenDisclosureAttemptException
     * @throws ExpiredAccessTokenException
     * @throws InvalidApplicationType
     * @throws InvalidOAuth2Request
     * @throws LockedClientException
     * @throws \oauth2\exceptions\InvalidClientCredentials
     * @throws \oauth2\exceptions\InvalidClientException
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
                $current_ip = IPHelper::getUserIp();
                $resource_server = $this->current_client->getResourceServer();
                //check if resource server is active
                if (!$resource_server->active)
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

            $issued_client = $this->client_service->getClientById($access_token->getClientId());

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
     * @throws InvalidOAuth2Request
     */
    public function getResponseType()
    {
        throw new InvalidOAuth2Request('Not Implemented!');
    }

    /**
     * @param OAuth2Request $request
     * @throws InvalidOAuth2Request
     */
    public function buildTokenRequest(OAuth2Request $request)
    {
        throw new InvalidOAuth2Request('Not Implemented!');
    }
}