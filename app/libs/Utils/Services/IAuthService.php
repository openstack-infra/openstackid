<?php namespace Utils\Services;
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
use OpenId\Models\IOpenIdUser;
/**
 * Interface IAuthService
 */
interface IAuthService
{
    // authorization responses

    const AuthorizationResponse_None         = "None";
    const AuthorizationResponse_AllowOnce    = "AllowOnce";
    const AuthorizationResponse_AllowForever = "AllowForever";
    const AuthorizationResponse_DenyForever  = "DenyForever";
    const AuthorizationResponse_DenyOnce     = "DenyOnce";

    // authentication responses

    const AuthenticationResponse_None        = "None";
    const AuthenticationResponse_Cancel      = "Cancel";

    /**
     * @return bool
     */
    public function isUserLogged();

    /**
     * @return IOpenIdUser
     */
    public function getCurrentUser();

    /**
     * @param string $username
     * @param string $password
     * @param bool $remember_me
     * @return mixed
     */
    public function login($username, $password, $remember_me);

    /**
     * @param string $username
     * @return IOpenIdUser
     */
    public function getUserByUsername($username);

    /**
     * @param int $id
     * @return IOpenIdUser
     */
    public function getUserById($id);

    public function getUserAuthorizationResponse();

    public function setUserAuthorizationResponse($auth_response);

    public function clearUserAuthorizationResponse();

    public function getUserAuthenticationResponse();

    public function setUserAuthenticationResponse($auth_response);

    public function clearUserAuthenticationResponse();

    /**
     * @return void
     */
    public function logout();

    /**
     * @param string $openid
     * @return IOpenIdUser
     */
    public function getUserByOpenId($openid);

    /**
     * @param int $user_id
     * @return string
     */
    public function unwrapUserId($user_id);

    /**
     * @param int $user_id
     * @param IClient $client
     * @return string
     */
    public function wrapUserId($user_id, IClient $client);

    /**
     * @param int $external_id
     * @return IOpenIdUser
     */
    public function getUserByExternalId($external_id);

    /**
     * @return string
     */
    public function getSessionId();

    /**
     * @param $client_id
     * @return void
     */
    public function registerRPLogin($client_id);

    /**
     * @return string[]
     */
    public function getLoggedRPs();

    /**
     * @param string $jti
     * @return void
     */
    public function reloadSession($jti);

}