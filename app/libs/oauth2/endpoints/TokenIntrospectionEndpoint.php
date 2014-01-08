<?php

namespace oauth2\endpoints;


use oauth2\exceptions\InvalidGrantTypeException;
use oauth2\requests\OAuth2Request;
use oauth2\IOAuth2Protocol;
use oauth2\services\IClientService;
use oauth2\services\ITokenService;
use utils\services\ILogService;
use oauth2\grant_types\ValidateBearerTokenGrantType;


class TokenIntrospectionEndpoint implements IOAuth2Endpoint  {

    private $protocol;
    private $grant_type;

    public function __construct(IOAuth2Protocol $protocol,  IClientService $client_service, ITokenService  $token_service, ILogService $log_service)
    {
        $this->protocol   = $protocol;
        $this->grant_type = new ValidateBearerTokenGrantType($client_service, $token_service, $log_service);
    }


    public function handle(OAuth2Request $request)
    {
        if($this->grant_type->canHandle($request))
        {
            return $this->grant_type->completeFlow($request);
        }
        throw new InvalidOAuth2Request;
    }
}