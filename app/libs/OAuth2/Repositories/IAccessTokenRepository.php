<?php namespace OAuth2\Repositories;
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

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Models\OAuth2\AccessToken;
use Models\OAuth2\RefreshToken;
use Utils\Db\IBaseRepository;

/**
 * Interface IAccessTokenRepository
 * @package OAuth2\Repositories
 */
interface IAccessTokenRepository extends IBaseRepository
{
    /**
     * @param int $client_identifier
     * @param int $page_nbr
     * @param int $page_size
     * @return LengthAwarePaginator
     */
    function getAllByClientIdentifier($client_identifier, $page_nbr = 1, $page_size = 10);

    /**
     * @param int $client_identifier
     * @param int $page_nbr
     * @param int $page_size
     * @return LengthAwarePaginator
     */
    function getAllValidByClientIdentifier($client_identifier, $page_nbr = 1, $page_size = 10);

    /**
     * @param int $user_id
     * @param int $page_nbr
     * @param int $page_size
     * @return LengthAwarePaginator
     */
    function getAllByUserId($user_id, $page_nbr = 1, $page_size = 10);

    /**
     * @param int $user_id
     * @param int $page_nbr
     * @param int $page_size
     * @return LengthAwarePaginator
     */
    function getAllValidByUserId($user_id, $page_nbr = 1, $page_size = 10);

    /**
     * @param string $hashed_value
     * @return AccessToken
     */
    function getByValue($hashed_value);

    /**
     * @param string $hashed_value
     * @return AccessToken
     */
    function getByAuthCode($hashed_value);

    /**
     * @param int $refresh_token_id
     * @return AccessToken[]
     */
    function getByRefreshToken($refresh_token_id);

}