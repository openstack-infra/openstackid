<?php

namespace oauth2\grant_types;

use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\exceptions\InvalidAccessTokenException;
use oauth2\exceptions\BearerTokenDisclosureAttemptException;

use oauth2\requests\OAuth2AccessTokenValidationRequest;
use oauth2\requests\OAuth2Request;
use oauth2\responses\OAuth2AccessTokenValidationResponse;
use oauth2\services\IClientService;
use oauth2\services\ITokenService;
use services\IPHelper;
use utils\services\ILogService;

use ReflectionClass;


/**
 * Class ValidateBearerTokenGrantType
 * @package oauth2\grant_types
 */
class ValidateBearerTokenGrantType extends AbstractGrantType
{

    const OAuth2Protocol_GrantType_Extension_ValidateBearerToken = 'urn:pingidentity.com:oauth2:grant_type:validate_bearer';

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

    public function getType()
    {
        return self::OAuth2Protocol_GrantType_Extension_ValidateBearerToken;
    }

    /** Not implemented , there is no first process phase on this grant type
     * @param OAuth2Request $request
     * @return mixed|void
     * @throws Exception
     */
    public function handle(OAuth2Request $request)
    {
        throw new InvalidOAuth2Request('Not Implemented!');
    }

    public function completeFlow(OAuth2Request $request)
    {
        $reflector = new ReflectionClass($request);
        $class_name = $reflector->getName();
        if ($class_name == 'oauth2\requests\OAuth2AccessTokenValidationRequest') {

            $token_value = $request->getToken();

            try{
                $access_token = $this->token_service->getAccessToken($token_value);

                //checks is current ip belongs to any registered resource server
                $current_ip   = IPHelper::getUserIp();
                if(!$this->token_service->checkAccessTokenAudience($access_token,$current_ip))
                    throw new BearerTokenDisclosureAttemptException(sprintf("Access Token %s was not emitted for ip %s",$token_value,$current_ip));

                return new OAuth2AccessTokenValidationResponse($token_value, $access_token->getScope(), $access_token->getAudience(),$access_token->getClientId(),$access_token->getRemainingLifetime());
            }
            catch(InvalidAccessTokenException $ex1){
                $this->log_service->error($ex1);
                throw new BearerTokenDisclosureAttemptException();
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
        $reflector  = new ReflectionClass($request);
        $class_name = $reflector->getName();
        if ($class_name == 'oauth2\requests\OAuth2TokenRequest') {
            if($request->getGrantType() !== $this->getType())
                return null;
            return new OAuth2AccessTokenValidationRequest($request->getMessage());
        }
        return null;
    }

}