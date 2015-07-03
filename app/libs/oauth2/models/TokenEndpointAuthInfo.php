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

namespace oauth2\models;

use jwa\cryptographic_algorithms\IntegrityCheckingAlgorithm;

/**
 * Class TokenEndpointAuthInfo
 * @package oauth2\models
 */
final class TokenEndpointAuthInfo {

    /**
     * @var string
     */
    private $auth_method;

    /**
     * @var IntegrityCheckingAlgorithm
     */
    private $auth_signing_alg;

    /**
     * @param $auth_method
     * @param IntegrityCheckingAlgorithm $auth_signing_alg
     */
    public function __construct($auth_method, IntegrityCheckingAlgorithm $auth_signing_alg = null)
    {
        $this->auth_method      = $auth_method;
        $this->auth_signing_alg = $auth_signing_alg;
    }

    /**
     * @return string
     */
    public function getAuthenticationMethod(){
        return $this->auth_method;
    }

    /**
     * @return IntegrityCheckingAlgorithm
     */
    public function getSigningAlgorithm(){
        return $this->auth_signing_alg;
    }

}