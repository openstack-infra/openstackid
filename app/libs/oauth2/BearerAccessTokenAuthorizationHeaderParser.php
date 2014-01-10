<?php

namespace oauth2;

use oauth2\exceptions\OAuth2MissingBearerAccessTokenException;
use string;

/**
 * Class BearerAccessTokenAuthorizationHeaderParser
 * Parse
 * http://tools.ietf.org/html/rfc6750#section-2-1
 * @package oauth2
 */
class BearerAccessTokenAuthorizationHeaderParser
{

    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new BearerAccessTokenAuthorizationHeaderParser();
        }
        return self::$instance;
    }

    /**
     * @param string $http_auth_header_value
     * @return string
     * @throws exceptions\OAuth2MissingBearerAccessTokenException
     */
    public function parse(string $http_auth_header_value)
    {
        if (is_null($http_auth_header_value) || empty($http_auth_header_value))

            // Check for special case, because cURL sometimes does an
            // internal second request and doubles the authorization header,
            // which always resulted in an error.
            //
            // 1st request: Authorization: Bearer XXX
            // 2nd request: Authorization: Bearer XXX, Bearer XXX
            if (strpos($http_auth_header_value, ',') !== false) {
                $headerPart = explode(',', $http_auth_header_value);
                $accessTokenValue = trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $headerPart[0]));
            } else {
                $accessTokenValue = trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $http_auth_header_value));
            }
        $accessTokenValue = ($accessTokenValue === 'Bearer') ? '' : $accessTokenValue;

        if (empty($accessToken)) {
            throw new OAuth2MissingBearerAccessTokenException;
        }

        return $accessTokenValue;

    }

    private function __clone()
    {
    }


} 