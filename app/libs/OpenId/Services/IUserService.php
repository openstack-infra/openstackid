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
/**
 * Interface IUserService
 * @package OpenId\Services
 */
interface IUserService
{

    /**
     * @param int $id
     * @return IOpenIdUser
     */
    public function get($id);

    /**
     * @param Member $member
     * @return IOpenIdUser
     */
    public function buildUser(Member $member);

    /**
     * @param $identifier
     * @return mixed
     */
    public function updateLastLoginDate($identifier);

    /**
     * @param $identifier
     * @return mixed
     */
    public function updateFailedLoginAttempts($identifier);

    /**
     * @param $identifier
     * @return mixed
     */
    public function lockUser($identifier);

    /**
     * @param $identifier
     * @return void
     */
    public function unlockUser($identifier);

    /**
     * @param $identifier
     * @return mixed
     */
    public function activateUser($identifier);

    /**
     * @param $identifier
     * @return mixed
     */
    public function deActivateUser($identifier);

    /**
     * @param $identifier
     * @param $show_pic
     * @param $show_full_name
     * @param $show_email
     * @return mixed
     */
    public function saveProfileInfo($identifier, $show_pic, $show_full_name, $show_email);

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr = 1, $page_size = 10, array $filters = array(), array $fields = array('*'));
}