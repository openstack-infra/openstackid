<?php namespace OAuth2;
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
use OAuth2\Exceptions\OAuth2MissingBearerAccessTokenException;
/**
 * Class BearerAccessTokenAuthorizationHeaderParser
 * Parse
 * @see http://tools.ietf.org/html/rfc6750#section-2-1
 * @package OAuth2
 */
class BearerAccessTokenAuthorizationHeaderParser
{
    /**
     * @var BearerAccessTokenAuthorizationHeaderParser
     */
    private static $instance = null;

    private function __construct(){}

    /**
     * @return null|BearerAccessTokenAuthorizationHeaderParser
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new BearerAccessTokenAuthorizationHeaderParser();
        }
        return self::$instance;
    }

    /**
     * @param string $http_auth_header_value
     * @return string
     * @throws OAuth2MissingBearerAccessTokenException
     */
    public function parse($http_auth_header_value)
    {
        $accessTokenValue = '';
        if (!is_null($http_auth_header_value) &&  !empty($http_auth_header_value)){
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
            $accessTokenValue = ($accessTokenValue == 'Bearer') ? '' : $accessTokenValue;
        }

        if (empty($accessTokenValue)) {
            throw new OAuth2MissingBearerAccessTokenException;
        }

        return $accessTokenValue;

    }

    private function __clone()
    {
    }


} 