<?php namespace OAuth2\Responses;

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

use Utils\Http\HttpContentType;

/**
 * Class OAuth2WWWAuthenticateErrorResponse
 * @see http://tools.ietf.org/html/rfc6750#section-3
 * @package OAuth2\Responses
 */
class OAuth2WWWAuthenticateErrorResponse extends OAuth2DirectResponse {

    /**
     * @var string
     */
    private $realm;
    /**
     * @var string
     */
    private $error;
    /**
     * @var string
     */
    private $error_description;
    /**
     * @var string
     */
    private $scope;
    /**
     * @var int
     */
    private $http_error;

    /**
     * OAuth2WWWAuthenticateErrorResponse constructor.
     * @param int $realm
     * @param string $error
     * @param $error_description
     * @param null| string $scope
     * @param int $http_error
     */
    public function __construct($realm, $error, $error_description, $scope = null, $http_error = self::HttpOkResponse){

        parent::__construct($http_error, HttpContentType::Json);

        $this->realm             = $realm;
        $this->error             = $error;
        $this->error_description = $error_description;
        $this->scope             = $scope;
        $this->http_error        = $http_error;
    }

    public function getWWWAuthenticateHeaderValue(){
        $value=sprintf('Bearer realm="%s"',$this->realm);
        $value=$value.sprintf(', error="%s"',$this->error);
        $value=$value.sprintf(', error_description="%s"',$this->error_description);
        if(!is_null($this->scope))
            $value=$value.sprintf(', scope="%s"',$this->scope);
        return $value;
    }

    /**
     * @return array
     */
    public function getContent()
    {
        $content = array(
            'error' => $this->error,
            'error_description' => $this->error_description
        );
        if(!is_null($this->scope))
           $content['scope'] = $this->scope;

        return $content;
    }

    public function getType()
    {
        return null;
    }
}