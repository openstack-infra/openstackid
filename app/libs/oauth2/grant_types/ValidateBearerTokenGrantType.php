<?php

namespace oauth2\grant_types;

use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\exceptions\InvalidAccessTokenException;
use oauth2\exceptions\BearerTokenDisclosureAttemptException;
use oauth2\exceptions\UnAuthorizedClientException;
use oauth2\exceptions\InvalidGrantTypeException;

use oauth2\requests\OAuth2Request;
use oauth2\responses\OAuth2AccessTokenValidationResponse;
use oauth2\services\IClientService;
use oauth2\services\ITokenService;
use services\IPHelper;
use utils\services\ILogService;
use oauth2\models\IClient;

use ReflectionClass;


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

    public function __construct(IClientService $client_service, ITokenService $token_service, ILogService $log_service)
    {
        parent::__construct($client_service, $token_service,$log_service);
    }

    public function canHandle(OAuth2Request $request)
    {
        $reflector = new ReflectionClass($request);
        $class_name = $reflector->getName();
        return $class_name == 'oauth2\requests\OAuth2AccessTokenValidationRequest' && $request->isValid();
    }

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
     * @return mixed|OAuth2AccessTokenValidationResponse|void
     * @throws \oauth2\exceptions\UnAuthorizedClientException
     * @throws \oauth2\exceptions\InvalidOAuth2Request
     * @throws \oauth2\exceptions\BearerTokenDisclosureAttemptException
     */
    public function completeFlow(OAuth2Request $request)
    {
        $reflector = new ReflectionClass($request);
        $class_name = $reflector->getName();
        if ($class_name == 'oauth2\requests\OAuth2AccessTokenValidationRequest') {

            parent::completeFlow($request);

            $token_value = $request->getToken();

            try{

                $access_token = $this->token_service->getAccessToken($token_value);

                if(!$this->current_client->isResourceServerClient()){
                    // if current client is not a resource server, then we could only access to our own tokens
                    if($access_token->getClientId()!== $this->current_client_id)
                        throw new BearerTokenDisclosureAttemptException(sprintf('access token %s does not belongs to client id %s',$token_value, $this->current_client_id));
                }
                else{
                    // current client is a resource server, validate client type (must be confidential)
                    if($this->current_client->getClientType()!== IClient::ClientType_Confidential)
                        throw new UnAuthorizedClientException('resource server client is not of confidential type!');
                    //validate resource server IP address
                    $current_ip= IPHelper::getUserIp();
                    $resource_server = $this->current_client->getResourceServer();
                    //check if resource server is active
                    if(!$resource_server->active)
                        throw new UnAuthorizedClientException('resource server is disabled!');
                    //check resource server ip address
                    if($current_ip !== $resource_server->ip)
                        throw new BearerTokenDisclosureAttemptException(sprintf('resource server ip (%s) differs from current request ip %s',$resource_server->ip,$current_ip));
                }

                return new OAuth2AccessTokenValidationResponse($token_value, $access_token->getScope(), $access_token->getAudience(),$access_token->getClientId(),$access_token->getRemainingLifetime());
            }
            catch(InvalidAccessTokenException $ex1){
                $this->log_service->error($ex1);
                throw new BearerTokenDisclosureAttemptException($ex1->getMessage());
            }
            catch(InvalidGrantTypeException $ex2){
                $this->log_service->error($ex2);
                throw new BearerTokenDisclosureAttemptException($ex2->getMessage());
            }
        }
        throw new InvalidOAuth2Request;
    }

    public function getResponseType()
    {
        throw new InvalidOAuth2Request('Not Implemented!');
    }

    public function buildTokenRequest(OAuth2Request $request)
    {
        throw new InvalidOAuth2Request('Not Implemented!');
    }

}