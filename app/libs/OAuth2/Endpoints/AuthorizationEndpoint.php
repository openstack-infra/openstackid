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

use OAuth2\Exceptions\AccessDeniedException;
use OAuth2\Exceptions\InvalidClientException;
use OAuth2\Exceptions\OAuth2GenericException;
use OAuth2\Exceptions\ScopeNotAllowedException;
use OAuth2\Exceptions\UnAuthorizedClientException;
use OAuth2\Exceptions\UnsupportedResponseTypeException;
use OAuth2\Exceptions\UriNotAllowedException;
use OAuth2\Requests\OAuth2Request;
use OAuth2\Exceptions\InvalidOAuth2Request;
use OAuth2\IOAuth2Protocol;

/**
 * Class AuthorizationEndpoint
 * Authorization Endpoint Implementation
 * The authorization endpoint is used to interact with the resource
 * owner and obtain an authorization grant.  The authorization server
 * MUST first verify the identity of the resource owner.  The way in
 * which the authorization server authenticates the resource owner
 * (e.g., username and password login, session cookies) is beyond the
 * scope of this specification.
 * @see http://tools.ietf.org/html/rfc6749#section-3.1
 * @package OAuth2\Endpoints
 */
class AuthorizationEndpoint implements IOAuth2Endpoint
{

    /**
     * @var IOAuth2Protocol
     */
    private $protocol;

    /**
     * @param IOAuth2Protocol $protocol
     */
    public function __construct(IOAuth2Protocol $protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * @param OAuth2Request $request
     * @return mixed
     * @throws InvalidOAuth2Request
     * @throws InvalidClientException
     * @throws UriNotAllowedException
     * @throws ScopeNotAllowedException
     * @throws UnsupportedResponseTypeException
     * @throws UnAuthorizedClientException
     * @throws AccessDeniedException
     * @throws OAuth2GenericException
     */
    public function handle(OAuth2Request $request)
    {
        foreach($this->protocol->getAvailableGrants() as $key => $grant){
            if($grant->canHandle($request))
                return $grant->handle($request);
        }
        throw new InvalidOAuth2Request;
    }
}