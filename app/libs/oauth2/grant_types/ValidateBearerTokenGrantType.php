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
use ReflectionClass;
use utils\services\ILogService;


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
        return $class_name == 'oauth2\requests\OAuth2TokenRequest' && $request->isValid();
    }

    public function getType()
    {
        return self::OAuth2Protocol_GrantType_Extension_ValidateBearerToken;
    }

    public function handle(OAuth2Request $request)
    {
        throw new Exception('Not Implemented!');
    }

    public function completeFlow(OAuth2Request $request)
    {
        $reflector = new ReflectionClass($request);
        $class_name = $reflector->getName();
        if ($class_name == 'oauth2\requests\OAuth2AccessTokenValidationRequest') {

            $token_value = $request->getToken();

            try{
                $access_token = $this->token_service->getAccessToken($token_value);
                return new OAuth2AccessTokenValidationResponse($token_value, $access_token->getScope(), $access_token->getAudience(),$access_token->getClientId());
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
        return null;
    }

    public function buildTokenRequest(OAuth2Request $request)
    {
        $reflector = new ReflectionClass($request);
        $class_name = $reflector->getName();
        if ($class_name == 'oauth2\requests\OAuth2TokenRequest') {
            if($request->getGrantType() !== $this->getType())
                return null;
            return new OAuth2AccessTokenValidationRequest($request->getMessage());
        }
        return null;
    }

}