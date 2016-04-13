<?php namespace OpenId\Services;
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
use OpenId\Models\IOpenIdUser;
use Models\Member;
use Utils\Exceptions\EntityNotFoundException;

/**
 * Interface IUserService
 * @package OpenId\Services
 */
interface IUserService
{

    /**
     * @param Member $member
     * @return IOpenIdUser
     */
    public function buildUser(Member $member);

    /**
     * @param int $user_id
     * @return void
     * @throws EntityNotFoundException
     */
    public function updateLastLoginDate($user_id);

    /**
     * @param int $user_id
     * @return void
     * @throws EntityNotFoundException
     */
    public function updateFailedLoginAttempts($user_id);

    /**
     * @param int $user_id
     * @return void
     * @throws EntityNotFoundException
     */
    public function lockUser($user_id);

    /**
     * @param $user_id
     * @return void
     * @throws EntityNotFoundException
     */
    public function unlockUser($user_id);

    /**
     * @param int $user_id
     * @return void
     * @throws EntityNotFoundException
     */
    public function activateUser($user_id);

    /**
     * @param int $user_id
     * @return void
     * @throws EntityNotFoundException
     */
    public function deActivateUser($user_id);

    /**
     * @param int $user_id
     * @param bool $show_pic
     * @param bool $show_full_name
     * @param bool $show_email
     * @return bool
     * @throws EntityNotFoundException
     */
    public function saveProfileInfo($user_id, $show_pic, $show_full_name, $show_email);

}