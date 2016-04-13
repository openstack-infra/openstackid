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

use OAuth2\Exceptions\InvalidGrantTypeException;
use OAuth2\IOAuth2Protocol;
use OAuth2\Requests\OAuth2Request;
use OAuth2\Responses\OAuth2Response;

/**
 * Class TokenEndpoint
 * Token Endpoint Implementation
 * The token endpoint is used by the client to obtain an access token by
 * presenting its authorization grant or refresh token.  The token
 * endpoint is used with every authorization grant except for the
 * implicit grant type (since an access token is issued directly).
 * @see http://tools.ietf.org/html/rfc6749#section-3.2
 * @package OAuth2\Endpoints
 */
class TokenEndpoint implements IOAuth2Endpoint
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
     * @return OAuth2Response
     * @throws InvalidGrantTypeException
     */
    public function handle(OAuth2Request $request)
    {
        foreach ($this->protocol->getAvailableGrants() as $key => $grant) {
            if ($grant->canHandle($request)) {
                $request = $grant->buildTokenRequest($request);
                if (is_null($request))
                    throw new InvalidGrantTypeException;
                return $grant->completeFlow($request);
            }
        }
        throw new InvalidGrantTypeException;
    }
}