<?php namespace OAuth2\ResourceServer;
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
use jwt\impl\JWTClaimSet;
/**
 * Interface IUserService
 * @package OAuth2\ResourceServer
 */
interface IUserService
{
    /**
     * This scope value requests access to the End-User's default profile Claims, which are:
     * name, family_name, given_name, middle_name, nickname, preferred_username, profile, picture,
     * website, gender, birthdate, zoneinfo, locale, and updated_at.
     */
    const UserProfileScope_Profile   = 'profile';
    /**
     * This scope value requests access to the email and email_verified Claims.
     */
    const UserProfileScope_Email     = 'email';
    /**
    * This scope value requests access to the address Claim.
    */
    const UserProfileScope_Address   = 'address';

    /**
     * @return mixed
     */
    public function getCurrentUserInfo();

    /**
     * @return JWTClaimSet
     */
    public function getCurrentUserInfoClaims();
}