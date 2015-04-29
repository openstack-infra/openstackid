<?php

namespace oauth2\grant_types;

use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\exceptions\UnAuthorizedClientException;
use oauth2\exceptions\BearerTokenDisclosureAttemptException;
use oauth2\exceptions\InvalidGrantTypeException;
use oauth2\exceptions\ExpiredAccessTokenException;

use oauth2\OAuth2Protocol;
use oauth2\requests\OAuth2Request;
use oauth2\responses\OAuth2TokenRevocationResponse;
use oauth2\services\IClientService;
use oauth2\services\ITokenService;

use ReflectionClass;
use utils\services\ILogService;

use Exception;

/**
 * Class RevokeTokenGrantType
 * http://tools.ietf.org/html/rfc7009
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

    public function __construct(IClientService $client_service, ITokenService $token_service, ILogService $log_service)
    {
        parent::__construct($client_service, $token_service, $log_service);
    }

    /** Given an OAuth2Request, returns true if it can handle it, false otherwise
     * @param OAuth2Request $request
     * @return boolean
     */
    public function canHandle(OAuth2Request $request)
    {
        $reflector = new ReflectionClass($request);
        $class_name = $reflector->getName();

        return $class_name == 'oauth2\requests\OAuth2TokenRevocationRequest' && $request->isValid();
    }

    /** defines entry point for first request processing
     * @param OAuth2Request $request
     * @throws \oauth2\exceptions\InvalidOAuth2Request
     * @return mixed
     */
    public function handle(OAuth2Request $request)
    {
        throw new InvalidOAuth2Request('not implemented!');
    }

    public function completeFlow(OAuth2Request $request)
    {

        $reflector = new ReflectionClass($request);
        $class_name = $reflector->getName();
        if ($class_name == 'oauth2\requests\OAuth2TokenRevocationRequest') {

            parent::completeFlow($request);
            $token_value = $request->getToken();
            $token_hint  = $request->getTokenHint();

            try{
                if (!is_null($token_hint) && !empty($token_hint)) {
                    //we have been provided with a token hint...
                    switch ($token_hint) {
                        case OAuth2Protocol::OAuth2Protocol_AccessToken:
                        {
                            //check ownership
                            $access_token = $this->token_service->getAccessToken($token_value);

                            if(is_null($access_token))
                                throw new ExpiredAccessTokenException(sprintf('Access token %s is expired!', $token_value));

                            if ($access_token->getClientId() !== $this->current_client_id)
                                throw new BearerTokenDisclosureAttemptException($this->current_client_id,sprintf('access token %s does not belongs to client id %s',$token_value, $this->current_client_id));

                            $this->token_service->revokeAccessToken($token_value, false);
                        }
                            break;
                        case OAuth2Protocol::OAuth2Protocol_RefreshToken:
                        {
                            //check ownership
                            $refresh_token = $this->token_service->getRefreshToken($token_value);

                            if ($refresh_token->getClientId() !== $this->current_client_id)
                                throw new BearerTokenDisclosureAttemptException($this->current_client_id,sprintf('refresh token %s does not belongs to client id %s',$token_value, $this->current_client_id));

                            $this->token_service->revokeRefreshToken($token_value, false);
                        }
                            break;
                    }
                } else {
                    /*
                     * no token hint given :(
                     * if the server is unable to locate the token using
                     * the given hint, it MUST extend its search across all of its
                     * supported token types.
                     */

                    //check and handle access token first ..
                    try{
                        //check ownership
                        $access_token = $this->token_service->getAccessToken($token_value);

                        if(is_null($access_token))
                            throw new ExpiredAccessTokenException(sprintf('Access token %s is expired!', $token_value));

                        if ($access_token->getClientId() !== $this->current_client_id)
                            throw new BearerTokenDisclosureAttemptException($this->current_client_id,sprintf('access token %s does not belongs to client id %s',$token_value, $this->current_client_id));

                        $this->token_service->revokeAccessToken($token_value, false);
                    }
                    catch(UnAuthorizedClientException $ex1){
                        $this->log_service->error($ex1);
                        throw $ex1;
                    }
                    catch(Exception $ex){
                        $this->log_service->warning($ex);
                        //access token was not found, check refresh token
                        //check ownership
                        $refresh_token = $this->token_service->getRefreshToken($token_value);
                        if ($refresh_token->getClientId() !== $this->current_client_id)
                            throw new BearerTokenDisclosureAttemptException($this->current_client_id,sprintf('refresh token %s does not belongs to client id %s',$token_value, $this->current_client_id));
                        $this->token_service->revokeRefreshToken($token_value, false);
                    }
                }
                return new OAuth2TokenRevocationResponse;
            }
            catch(InvalidGrantTypeException $ex){
                throw new BearerTokenDisclosureAttemptException($this->current_client_id,$ex->getMessage());
            }
        }
        throw new InvalidOAuth2Request;
    }

    /**
     * get grant type
     * @return mixed
     */
    public function getType()
    {
        return self::OAuth2Protocol_GrantType_Extension_RevokeToken;
    }

    /** get grant type response type
     * @return mixed
     * @throws \oauth2\exceptions\InvalidOAuth2Request
     */
    public function getResponseType()
    {
        throw new InvalidOAuth2Request('not implemented!');
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