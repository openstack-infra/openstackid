<?php namespace OAuth2\Responses;

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

use Utils\Http\HttpContentType;

/**
 * Class OAuth2TokenRevocationResponse
 * @see http://tools.ietf.org/html/rfc7009#section-2.2
 * The authorization server responds with HTTP status code 200 if the
 * token has been revoked successfully or if the client submitted an
 * invalid token.
 * Note: invalid tokens do not cause an error response since the client
 * cannot handle such an error in a reasonable way.  Moreover, the
 * purpose of the revocation request, invalidating the particular token,
 * is already achieved.
 * The content of the response body is ignored by the client as all
 * necessary information is conveyed in the response code.
 * An invalid token type hint value is ignored by the authorization
 * server and does not influence the revocation response.
 * @package OAuth2\Responses
 */
class OAuth2TokenRevocationResponse extends OAuth2DirectResponse {

    public function __construct()
    {
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct(self::HttpOkResponse, HttpContentType::Json);
    }
} 