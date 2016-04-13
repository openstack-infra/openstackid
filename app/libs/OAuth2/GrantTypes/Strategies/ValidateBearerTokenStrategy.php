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
use OAuth2\Models\ClientAuthenticationContext;
use OAuth2\Models\IClient;

/**
 * Class ValidateBearerTokenStrategy
 * @package OAuth2\GrantTypes\Strategies
 */
final class ValidateBearerTokenStrategy implements IValidateBearerTokenStrategy
{

    /**
     * @var ClientAuthenticationContext
     */
    private $client_auth_context;

    public function __construct(ClientAuthenticationContext $client_auth_context)
    {
        $this->client_auth_context = $client_auth_context;
    }

    /**
     * @param AccessToken $access_token
     * @param IClient $client
     * @throws BearerTokenDisclosureAttemptException
     */
    public function validate(AccessToken $access_token, IClient $client)
    {
        // if current client is not a resource server, then we could only access to our own tokens
        if ($access_token->getClientId() !== $this->client_auth_context->getId())
        {
            throw new BearerTokenDisclosureAttemptException
            (
                sprintf
                (
                    'access token %s does not belongs to client id %s',
                    $access_token->getValue(),
                    $this->client_auth_context->getId()
                )
            );
        }
    }
}