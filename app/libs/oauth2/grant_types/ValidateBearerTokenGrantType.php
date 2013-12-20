<?php
namespace oauth2\grant_types;


use oauth2\exceptions\UnAuthorizedClientException;
use oauth2\requests\OAuth2Request;
use oauth2\services\IClientService;
use oauth2\services\ITokenService;
use oauth2\responses\OAuth2AccessTokenValidationResponse;
/**
 * Class ValidateBearerTokenGrantType
 * @package oauth2\grant_types
 */
class ValidateBearerTokenGrantType extends AbstractGrantType{

    const OAuth2Protocol_GrantType_Extension_ValidateBearerToken = 'urn:pingidentity.com:oauth2:grant_type:validate_bearer';


    public function __construct(IClientService $client_service, ITokenService $token_service)
    {
        parent::__construct($client_service,$token_service);
    }

    public function canHandle(OAuth2Request $request)
    {
        $reflector = new ReflectionClass($request);
        $class_name = $reflector->getName();
        return $class_name == 'oauth2\requests\OAuth2AccessTokenValidationRequest' && $request->isValid();
    }

    public function handle(OAuth2Request $request)
    {
        throw new Exception('Not Implemented!');
    }

    public function completeFlow(OAuth2Request $request)
    {
        parent::completeFlow($request);

        $token_value  = $request->getToken();

        $access_token = $this->token_service->getAccessToken($token_value);

        if($access_token->getClientId() !== $this->current_client_id)
            throw new UnAuthorizedClientException();

        return new OAuth2AccessTokenValidationResponse($access_token->getValue(),$access_token->getScope(),$access_token->getAudience());
    }

    public function getType()
    {
        return self::OAuth2Protocol_GrantType_Extension_ValidateBearerToken;
    }

}