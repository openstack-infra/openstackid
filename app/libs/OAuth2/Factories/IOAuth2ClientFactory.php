<?php namespace OAuth2\Factories;
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
use OAuth2\Models\IClient;
/**
 * Interface IOAuth2ClientFactory
 * @package OAuth2\Factories
 */
interface IOAuth2ClientFactory
{
    /**
     * @param string $app_name
     * @param $owner
     * @param string $application_type
     * @return IClient
     */
    public function build($app_name, $owner, $application_type);
}