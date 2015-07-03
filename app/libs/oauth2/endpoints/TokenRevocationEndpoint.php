<?php

namespace oauth2\endpoints;

use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\requests\OAuth2Request;
use oauth2\IOAuth2Protocol;
use oauth2\services\IClientService;
use oauth2\services\ITokenService;
use utils\services\ILogService;
use oauth2\grant_types\RevokeBearerTokenGrantType;

/**
 * Class TokenRevocationEndpoint
 * @package oauth2\endpoints
 */
class TokenRevocationEndpoint implements IOAuth2Endpoint
{

    /**
     * @var IOAuth2Protocol
     */
    private $protocol;
    /**
     * @var RevokeBearerTokenGrantType
     */
    private $grant_type;

    /**
     * @param IOAuth2Protocol $protocol
     * @param IClientService $client_service
     * @param ITokenService $token_service
     * @param ILogService $log_service
     */
    public function __construct(
        IOAuth2Protocol $protocol,
        IClientService $client_service,
        ITokenService $token_service,
        ILogService $log_service
    )
    {
        $this->protocol   = $protocol;
        $this->grant_type = new RevokeBearerTokenGrantType($client_service, $token_service, $log_service);
    }

    /**
     * @param OAuth2Request $request
     * @return \oauth2\responses\OAuth2TokenRevocationResponse
     * @throws InvalidOAuth2Request
     * @throws \Exception
     * @throws \oauth2\exceptions\BearerTokenDisclosureAttemptException
     * @throws \oauth2\exceptions\ExpiredAccessTokenException
     * @throws \oauth2\exceptions\UnAuthorizedClientException
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