<?php

namespace oauth2\endpoints;

use oauth2\exceptions\InvalidGrantTypeException;
use oauth2\IOAuth2Protocol;
use oauth2\requests\OAuth2Request;


/**
 * Class TokenEndpoint
 * Token Endpoint Implementation
 * The token endpoint is used by the client to obtain an access token by
 * presenting its authorization grant or refresh token.  The token
 * endpoint is used with every authorization grant except for the
 * implicit grant type (since an access token is issued directly).
 * http://tools.ietf.org/html/rfc6749#section-3.2
 * @package oauth2\endpoints
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
     * @return mixed
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