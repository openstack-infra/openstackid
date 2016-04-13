<?php namespace OAuth2\GrantTypes\Strategies;
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

use OAuth2\Models\AccessToken;
use OAuth2\Exceptions\BearerTokenDisclosureAttemptException;
use OAuth2\Exceptions\InvalidApplicationType;
use OAuth2\Exceptions\LockedClientException;
use OAuth2\Models\IClient;
use OAuth2\Services\ITokenService;
use Utils\IPHelper;

/**
 * Class ValidateBearerTokenResourceServerStrategy
 * @package OAuth2\GrantTypes\Strategies
 */
final class ValidateBearerTokenResourceServerStrategy implements IValidateBearerTokenStrategy
{
    /**
     * @var ITokenService
     */
    private $token_service;

    /**
     * ValidateBearerTokenResourceServerStrategy constructor.
     * @param ITokenService $token_service
     */
    public function __construct(ITokenService $token_service)
    {
        $this->token_service = $token_service;
    }

    /**
     * @param AccessToken $access_token
     * @param IClient $client
     * @throws BearerTokenDisclosureAttemptException
     * @throws InvalidApplicationType
     * @throws LockedClientException
     */
    public function validate(AccessToken $access_token, IClient $client)
    {
        // current client is a resource server, validate client type (must be confidential)
        if ($client->getClientType() !== IClient::ClientType_Confidential)
        {
            throw new InvalidApplicationType
            (
                'resource server client is not of confidential type!'
            );
        }
        //validate resource server IP address
        $current_ip       = IPHelper::getUserIp();
        $resource_server  = $client->getResourceServer();
        //check if resource server is active
        if (!$resource_server->isActive())
        {
            throw new LockedClientException
            (
                'resource server is disabled!'
            );
        }
        //check resource server ip address
        if (!$resource_server->isOwn($current_ip))
        {
            throw new BearerTokenDisclosureAttemptException
            (
                sprintf
                (
                    'resource server ip (%s) differs from current request ip %s',
                    $resource_server->getIPAddresses(),
                    $current_ip
                )
            );
        }
        // check if current ip belongs to a registered resource server audience
        if (!$this->token_service->checkAccessTokenAudience($access_token, $current_ip))
        {
            throw new BearerTokenDisclosureAttemptException
            (
                sprintf
                (
                    'access token current audience does not match with current request ip %s',
                    $current_ip
                )
            );
        }
    }
}