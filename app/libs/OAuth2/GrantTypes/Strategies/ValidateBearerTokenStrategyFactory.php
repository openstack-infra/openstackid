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
use OAuth2\Models\ClientAuthenticationContext;
use OAuth2\Models\IClient;
use OAuth2\Services\ITokenService;

/**
 * Class ValidateBearerTokenStrategyFactory
 * @package OAuth2\GrantTypes\Strategies
 */
final class ValidateBearerTokenStrategyFactory
{


    /**
     * @param ClientAuthenticationContext $client_auth_context
     * @param ITokenService $token_service
     * @param IClient $client
     * @return ValidateBearerTokenResourceServerStrategy|ValidateBearerTokenStrategy
     */
    public static function build(ClientAuthenticationContext $client_auth_context, ITokenService $token_service, IClient $client){
        if($client->isResourceServerClient())
            return new ValidateBearerTokenResourceServerStrategy($token_service);
        return new ValidateBearerTokenStrategy($client_auth_context);
    }
}