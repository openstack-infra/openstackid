<?php

namespace utils\services;

use oauth2\models\IClient;
use openid\model\IOpenIdUser;

/**
 * Interface IAuthService
 * @package utils\services
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
     * @param $username
     * @param $password
     * @param $remember_me
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
    public function getUserByExternaldId($external_id);

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
     * @param string $session_state
     * @return void
     */
    public function reloadSession($session_state);

}