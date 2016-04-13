<?php namespace OAuth2\Exceptions;
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
use Exception;
/**
 * Class OAuth2ResourceServerException
 * @package OAuth2\Exceptions
 */
class OAuth2ResourceServerException extends Exception
{

    /**
     * @var string
     */
    private $http_code;
    /**
     * @var int
     */
    private $error;
    /**
     * @var Exception
     */
    private $error_description;
    /**
     * @var null
     */
    private $scope;

    /**
     * @param string $http_code
     * @param int $error
     * @param string $error_description
     * @param null|string $scope
     */
    public function __construct($http_code, $error,$error_description,$scope = null)
    {
        $this->http_code         = $http_code;
        $this->error             = $error;
        $this->error_description = $error_description;
        $this->scope             = $scope;

        $message = "Resource Server Exception : " . sprintf
            (
                'http code : %s  - error : %s - error description: %s',
                $http_code,
                $error,
                $error_description
            );

        parent::__construct($message, 0, null);
    }

    /**
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getErrorDescription()
    {
        return $this->error_description;
    }

    /**
     * @return null|string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function getHttpCode()
    {
        return $this->http_code;
    }
} 