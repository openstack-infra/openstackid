<?php

namespace oauth2\endpoints;

use oauth2\requests\OAuth2Request;
use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\IOAuth2Protocol;
use oauth2\exceptions\InvalidGrantTypeException;

/**
 * Class AuthorizationEndpoint
 * Authorization Endpoint Implementation
 * http://tools.ietf.org/html/rfc6749#section-3.1
 * @package oauth2\endpoints
 */
class AuthorizationEndpoint implements IOAuth2Endpoint {


    private $protocol;
    public function __construct(IOAuth2Protocol $protocol){
        $this->protocol = $protocol;
    }

    /**
     * @param OAuth2Request $request
     * @return mixed
     * @throws \oauth2\exceptions\InvalidOAuth2Request
     * @throws \oauth2\exceptions\InvalidClientException
     * @throws \oauth2\exceptions\UriNotAllowedException
     * @throws \oauth2\exceptions\ScopeNotAllowedException
     * @throws \oauth2\exceptions\UnsupportedResponseTypeException
     * @throws \oauth2\exceptions\UnAuthorizedClientException
     * @throws \oauth2\exceptions\AccessDeniedException
     * @throws \oauth2\exceptions\OAuth2GenericException
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