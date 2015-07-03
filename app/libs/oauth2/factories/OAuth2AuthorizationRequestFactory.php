<?php
/**
 * Copyright 2015 OpenStack Foundation
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

namespace oauth2\factories;

use oauth2\exceptions\InvalidAuthenticationRequestException;
use oauth2\exceptions\InvalidAuthorizationRequestException;
use oauth2\OAuth2Protocol;
use oauth2\requests\OAuth2AuthenticationRequest;
use oauth2\requests\OAuth2AuthorizationRequest;
use oauth2\requests\OAuth2RequestMemento;
use oauth2\OAuth2Message;
use oauth2\services\IMementoOAuth2SerializerService;

/**
 * Class OAuth2AuthorizationRequestFactory
 * @package oauth2\factories
 */

final class OAuth2AuthorizationRequestFactory
{
    /**
     * @param OAuth2Message $msg
     * @return OAuth2AuthenticationRequest|OAuth2AuthorizationRequest
     * @throws InvalidAuthenticationRequestException
     * @throws InvalidAuthorizationRequestException
     */
    public function build(OAuth2Message $msg){

        $auth_request = new OAuth2AuthorizationRequest($msg);

        if(!$auth_request->isValid()){
            throw new InvalidAuthorizationRequestException($auth_request->toString());
        }

        if( str_contains($auth_request->getScope(), OAuth2Protocol::OpenIdConnect_Scope) ) {
            $auth_request = new OAuth2AuthenticationRequest($auth_request);
            if(!$auth_request->isValid()){
                throw new InvalidAuthenticationRequestException;
            }
        }
        return  $auth_request;
    }

    /**
     * @var OAuth2AuthorizationRequestFactory
     */
    private static $instance;

    private function __construct(){}

    private function __clone(){}

    /**
     * @return OAuth2AuthorizationRequestFactory
     */
    public static function getInstance()
    {
        if(!is_object(self::$instance))
        {
            self::$instance = new OAuth2AuthorizationRequestFactory();
        }
        return self::$instance;
    }

}