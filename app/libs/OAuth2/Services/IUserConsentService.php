<?php namespace OAuth2\Services;
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
use OAuth2\Models\IUserConsent;
use Utils\Exceptions\EntityNotFoundException;

/**
 * Interface IUserConsentService
 * @package OAuth2\Services
 */
interface IUserConsentService
{
    /**
     * @param int $user_id
     * @param string $client_id
     * @param string $scopes
     * @return IUserConsent
     */
    public function get($user_id, $client_id, $scopes);

    /**
     * @param int  $user_id
     * @param string $client_id
     * @param string $scopes
     * @return IUserConsent
     * @throws EntityNotFoundException
     */
    public function add($user_id, $client_id, $scopes);
} 