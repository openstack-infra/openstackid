<?php

namespace utils\services;


interface IAuthService
{
    const AuthorizationResponse_None         = "None";
    const AuthorizationResponse_AllowOnce    = "AllowOnce";
    const AuthorizationResponse_AllowForever = "AllowForever";
    const AuthorizationResponse_DenyForever  = "DenyForever";
    const AuthorizationResponse_DenyOnce     = "DenyOnce";

	const AuthenticationResponse_None         = "None";
	const AuthenticationResponse_Cancel       = "Cancel";

    /**
     * @return bool
     */
    public function isUserLogged();

    public function getCurrentUser();
    /**
     * @param $username
     * @param $password
     * @param $remember_me
     * @return mixed
     */
    public function login($username, $password, $remember_me);

    public function getUserByUsername($username);

    public function getUserById($id);

    public function getUserAuthorizationResponse();

    public function setUserAuthorizationResponse($auth_response);

	public function clearUserAuthorizationResponse();

	public function getUserAuthenticationResponse();

	public function setUserAuthenticationResponse($auth_response);

	public function clearUserAuthenticationResponse();

    public function logout();

    public function getUserByOpenId($openid);
}