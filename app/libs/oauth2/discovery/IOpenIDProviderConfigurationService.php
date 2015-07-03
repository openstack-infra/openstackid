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

namespace oauth2\discovery;

/**
 * Interface IOpenIDProviderConfigurationService
 * @package oauth2\discovery
 */
interface IOpenIDProviderConfigurationService
{
    /**
     * @return string
     */
    public function getIssuerUrl();

    /**
     * @return string
     */
    public function getAuthEndpoint();

    /**
     * @return string
     */
    public function getTokenEndpoint();

    /**
     * @return string
     */
    public function getUserInfoEndpoint();

    /**
     * @return string
     */
    public function getJWKSUrl();

    /**
     * @return string
     */
    public function getRevocationEndpoint();

    /**
     * @return string
     */
    public function getIntrospectionEndpoint();
}