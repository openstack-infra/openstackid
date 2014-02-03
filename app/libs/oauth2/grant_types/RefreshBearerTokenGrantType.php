<?php

namespace oauth2\grant_types;

use Exception;
use oauth2\models\IClient;
use oauth2\requests\OAuth2Request;
use oauth2\OAuth2Protocol;
use oauth2\services\IClientService;
use oauth2\services\ITokenService;
use utils\services\ILogService;
use oauth2\requests\OAuth2RefreshAccessTokenRequest;

use ReflectionClass;

use oauth2\exceptions\InvalidApplicationType;
use oauth2\exceptions\UseRefreshTokenException;
use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\exceptions\InvalidGrantTypeException;

use oauth2\responses\OAuth2AccessTokenResponse;


/**
 * Class RefreshBearerTokenGrantType
 * http://tools.ietf.org/html/rfc6749#section-6
 * @package oauth2\grant_types
 */

class RefreshBearerTokenGrantType extends AbstractGrantType {

    public function __construct(IClientService $client_service, ITokenService $token_service, ILogService $log_service)
    {
        parent::__construct($client_service, $token_service,$log_service);
    }


    public function canHandle(OAuth2Request $request)
    {
        $reflector = new ReflectionClass($request);
        $class_name = $reflector->getName();
        return $class_name == 'oauth2\requests\OAuth2TokenRequest' && $request->isValid() && $request->getGrantType() === $this->getType();
    }

    /** Not implemented , there is no first process phase on this grant type
     * @param OAuth2Request $request
     * @return mixed|void
     * @throws \Exception
     */
    public function handle(OAuth2Request $request)
    {
        throw new InvalidOAuth2Request('not implemented!');
    }



    /**
     * Access Token issuance using a refresh token
     * The authorization server MUST:
     *
     *  o  require client authentication for confidential clients or for any
     *     client that was issued client credentials (or with other
     *     authentication requirements),
     *  o  authenticate the client if client authentication is included and
     *     ensure that the refresh token was issued to the authenticated
     *     client, and
     * o  validate the refresh token.
     *
     * @param OAuth2Request $request
     * @return mixed|OAuth2AccessTokenResponse|void
     * @throws \oauth2\exceptions\UseRefreshTokenException
     * @throws \oauth2\exceptions\InvalidOAuth2Request
     * @throws \oauth2\exceptions\InvalidApplicationType
     * @throws \oauth2\exceptions\InvalidGrantTypeException
     */
    public function completeFlow(OAuth2Request $request)
    {
        $reflector = new ReflectionClass($request);
        $class_name = $reflector->getName();
        if ($class_name == 'oauth2\requests\OAuth2RefreshAccessTokenRequest') {

            parent::completeFlow($request);

            if($this->current_client->getApplicationType()!=IClient::ApplicationType_Web_App)
                throw new InvalidApplicationType($this->current_client_id,sprintf('client id %s client type must be WEB_APPLICATION',$this->current_client_id));

            if(!$this->current_client->use_refresh_token)
                throw new UseRefreshTokenException("current client id %s could not use refresh tokens",$this->current_client_id);

            $refresh_token_value = $request->getRefreshToken();
            $scope               = $request->getScope();
            $refresh_token       = $this->token_service->getRefreshToken($refresh_token_value);

            if(is_null($refresh_token))
                throw new InvalidGrantTypeException(sprintf("refresh token %s does not exists!",$refresh_token_value));

            if($refresh_token->getClientId() !== $this->current_client->client_id)
                throw new InvalidGrantTypeException(sprintf("refresh token %s does not belongs to client %s",$refresh_token_value,$this->current_client->client_id));

            $access_token = $this->token_service->createAccessTokenFromRefreshToken($refresh_token, $scope);

            $new_refresh_token = null;
            /*
             * the authorization server could employ refresh token
             * rotation in which a new refresh token is issued with every access
             * token refresh response.  The previous refresh token is invalidated
             * but retained by the authorization server.  If a refresh token is
             * compromised and subsequently used by both the attacker and the
             * legitimate client, one of them will present an invalidated refresh
             * token, which will inform the authorization server of the breach.
             */
            if($this->current_client->rotate_refresh_token){
                $this->token_service->invalidateRefreshToken($refresh_token_value);
                $new_refresh_token = $this->token_service->createRefreshToken($access_token);
            }

            $response = new OAuth2AccessTokenResponse($access_token->getValue(), $access_token->getLifetime(), !is_null($new_refresh_token) ? $new_refresh_token->getValue() : $scope);
            return $response;

        }
        throw new InvalidOAuth2Request;
    }

    public function getType()
    {
        return OAuth2Protocol::OAuth2Protocol_GrantType_RefreshToken;
    }

    public function getResponseType()
    {
        throw new InvalidOAuth2Request('not implemented!');
    }

    public function buildTokenRequest(OAuth2Request $request)
    {
        $reflector  = new ReflectionClass($request);
        $class_name = $reflector->getName();
        if ($class_name == 'oauth2\requests\OAuth2TokenRequest') {
            if($request->getGrantType() !== $this->getType())
                return null;
            return new OAuth2RefreshAccessTokenRequest($request->getMessage());
        }
        return null;
    }
}