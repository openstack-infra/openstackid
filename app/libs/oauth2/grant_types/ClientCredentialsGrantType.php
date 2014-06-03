<?php

namespace oauth2\grant_types;

use oauth2\exceptions\InvalidGrantTypeException;
use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\exceptions\ScopeNotAllowedException;
use oauth2\exceptions\InvalidApplicationType;

use oauth2\models\IClient;
use oauth2\OAuth2Protocol;
use oauth2\requests\OAuth2AccessTokenRequestClientCredentials;

use oauth2\requests\OAuth2Request;
use oauth2\responses\OAuth2AccessTokenResponse;
use oauth2\services\IApiScopeService;
use oauth2\services\IClientService;

use oauth2\services\ITokenService;
use ReflectionClass;
use utils\services\ILogService;

/**
 * Class ClientCredentialsGrantType
 * The client can request an access token using only its client
 * credentials (or other supported means of authentication) when the
 * client is requesting access to the protected resources under its
 * control, or those of another resource owner that have been previously
 * arranged with the authorization server (the method of which is beyond
 * the scope of this specification).
 * http://tools.ietf.org/html/rfc6749#section-4.4
 * @package oauth2\grant_types
 */
class ClientCredentialsGrantType extends AbstractGrantType
{


    private $scope_service;

    public function __construct(IApiScopeService $scope_service, IClientService $client_service, ITokenService $token_service, ILogService $log_service)
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
        $reflector = new ReflectionClass($request);
        $class_name = $reflector->getName();
        return
            ($class_name == 'oauth2\requests\OAuth2TokenRequest' && $request->isValid() &&  $request->getGrantType() == $this->getType());
    }


    /**
     * get grant type response type
     * @return mixed|void
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
    public function handle(OAuth2Request $request)
    {
        throw new InvalidOAuth2Request('not implemented!');
    }

    /**
     * @param OAuth2Request $request
     * @return mixed|OAuth2AccessTokenResponse|void
     * @throws \oauth2\exceptions\ScopeNotAllowedException
     * @throws \oauth2\exceptions\InvalidOAuth2Request
     * @throws \oauth2\exceptions\InvalidApplicationType
     * @throws \oauth2\exceptions\InvalidGrantTypeException
     */
    public function completeFlow(OAuth2Request $request)
    {
        $reflector = new ReflectionClass($request);
        $class_name = $reflector->getName();
        if ($class_name == 'oauth2\requests\OAuth2AccessTokenRequestClientCredentials') {

            if($request->getGrantType()!=$this->getType())
                throw new InvalidGrantTypeException;

            parent::completeFlow($request);

            //only confidential clients could use this grant type
            if ($this->current_client->getApplicationType() != IClient::ApplicationType_Service)
                throw new InvalidApplicationType($this->current_client_id,sprintf('client id %s client type must be SERVICE',$this->current_client_id));

            //check requested scope
            $scope = $request->getScope();
            if (is_null($scope) || empty($scope) || !$this->current_client->isScopeAllowed($scope))
                throw new ScopeNotAllowedException(sprintf("scope %s", $scope));

            // build current audience ...
            $audience = $this->scope_service->getStrAudienceByScopeNames(explode(' ', $scope));

            //build access token
            $access_token = $this->token_service->createAccessTokenFromParams($this->current_client_id,$scope, $audience);

            $response = new OAuth2AccessTokenResponse($access_token->getValue(), $access_token->getLifetime(), null);
            return $response;
        }
        throw new InvalidOAuth2Request;
    }

    /** builds specific Token request
     * @param OAuth2Request $request
     * @return mixed
     */
    public function buildTokenRequest(OAuth2Request $request)
    {
        $reflector = new ReflectionClass($request);
        $class_name = $reflector->getName();
        if ($class_name == 'oauth2\requests\OAuth2TokenRequest') {
            if ($request->getGrantType() !== $this->getType())
                return null;
            return new OAuth2AccessTokenRequestClientCredentials($request->getMessage());
        }
        return null;
    }

    /**
     * get grant type
     * @return mixed
     */
    public function getType()
    {
        return OAuth2Protocol::OAuth2Protocol_GrantType_ClientCredentials;
    }
}