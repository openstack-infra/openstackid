<?php namespace OAuth2\Endpoints;

/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

use OAuth2\Exceptions\BearerTokenDisclosureAttemptException;
use OAuth2\Exceptions\ExpiredAccessTokenException;
use OAuth2\Exceptions\InvalidApplicationType;
use OAuth2\Exceptions\LockedClientException;
use OAuth2\Repositories\IClientRepository;
use OAuth2\Requests\OAuth2Request;
use OAuth2\IOAuth2Protocol;
use OAuth2\Responses\OAuth2Response;
use OAuth2\Services\IClientService;
use OAuth2\Services\ITokenService;
use Utils\Services\IAuthService;
use Utils\Services\ILogService;
use OAuth2\GrantTypes\ValidateBearerTokenGrantType;
use OAuth2\Exceptions\InvalidOAuth2Request;

/**
 * Class TokenIntrospectionEndpoint
 * @package OAuth2\Endpoints
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
     * TokenIntrospectionEndpoint constructor.
     * @param IOAuth2Protocol $protocol
     * @param IClientService $client_service
     * @param IClientRepository $client_repository
     * @param ITokenService $token_service
     * @param IAuthService $auth_service
     * @param ILogService $log_service
     */
    public function __construct
    (
        IOAuth2Protocol $protocol,
        IClientService $client_service,
        IClientRepository $client_repository,
        ITokenService $token_service,
        IAuthService $auth_service,
        ILogService $log_service
    )
    {
        $this->protocol   = $protocol;
        $this->grant_type = new ValidateBearerTokenGrantType($client_service, $client_repository, $token_service, $auth_service, $log_service);
    }


    /**
     * @param OAuth2Request $request
     * @return OAuth2Response
     * @throws InvalidOAuth2Request
     * @throws BearerTokenDisclosureAttemptException
     * @throws ExpiredAccessTokenException
     * @throws InvalidApplicationType
     * @throws InvalidOAuth2Request
     * @throws LockedClientException
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