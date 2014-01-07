<?php

namespace oauth2\responses;

/**
 * Class OAuth2TokenRevocationResponse
 * http://tools.ietf.org/html/rfc7009#section-2.2
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
 * @package oauth2\responses
 */
class OAuth2TokenRevocationResponse extends OAuth2DirectResponse {

    public function __construct()
    {
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct(self::HttpOkResponse, self::DirectResponseContentType);
    }
} 