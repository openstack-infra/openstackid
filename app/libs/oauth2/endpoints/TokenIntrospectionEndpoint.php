<?php

namespace oauth2\endpoints;

use oauth2\requests\OAuth2Request;
use oauth2\IOAuth2Protocol;
use oauth2\services\IClientService;
use oauth2\services\ITokenService;
use utils\services\IAuthService;
use utils\services\ILogService;
use oauth2\grant_types\ValidateBearerTokenGrantType;
use oauth2\exceptions\InvalidOAuth2Request;

/**
 * Class TokenIntrospectionEndpoint
 * @package oauth2\endpoints
 */
class TokenIntrospectionEndpoint implements IOAuth2Endpoint
{

    /**
     * @var IOAuth2Protocol
     */
    private $protocol;
    /**
     * @var ValidateBearerTokenGrantType
     */
    private $grant_type;

    /**
     * @param IOAuth2Protocol $protocol
     * @param IClientService $client_service
     * @param ITokenService $token_service
     * @param IAuthService $auth_service
     * @param ILogService $log_service
     */
    public function __construct
    (
        IOAuth2Protocol $protocol,
        IClientService $client_service,
        ITokenService $token_service,
        IAuthService $auth_service,
        ILogService $log_service
    )
    {
        $this->protocol   = $protocol;
        $this->grant_type = new ValidateBearerTokenGrantType($client_service, $token_service, $auth_service, $log_service);
    }


    /**
     * @param OAuth2Request $request
     * @return mixed|\oauth2\responses\OAuth2AccessTokenValidationResponse|void
     * @throws InvalidOAuth2Request
     * @throws \oauth2\exceptions\BearerTokenDisclosureAttemptException
     * @throws \oauth2\exceptions\ExpiredAccessTokenException
     * @throws \oauth2\exceptions\InvalidApplicationType
     * @throws \oauth2\exceptions\InvalidOAuth2Request
     * @throws \oauth2\exceptions\LockedClientException
     */
    public function handle(OAuth2Request $request)
    {
        if($this->grant_type->canHandle($request))
        {
            return $this->grant_type->completeFlow($request);
        }
        throw new InvalidOAuth2Request;
    }
}