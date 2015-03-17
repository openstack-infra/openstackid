<?php

namespace auth;

use Auth;
use Session;
use utils\services\IAuthService;
use \Member;

class AuthService implements IAuthService
{

    /**
     * @return mixed
     */
    public function isUserLogged()
    {
        return Auth::check();
    }

    /**
     * @return mixed
     */
    public function getCurrentUser()
    {
        return Auth::user();
    }

    /**
     * @param $username
     * @param $password
     * @param $remember_me
     * @return mixed
     */
    public function login($username, $password, $remember_me)
    {
        return Auth::attempt(array('username' => $username, 'password' => $password), $remember_me);
    }

    public function logout()
    {
        Auth::logout();
    }

    /**
     * @return AuthorizationResponse_*
     */
    public function getUserAuthorizationResponse()
    {
        if (Session::has("openid.authorization.response")) {
            $value = Session::get("openid.authorization.response");
            return $value;
        }
        return IAuthService::AuthorizationResponse_None;
    }

    public function clearUserAuthorizationResponse(){
        if (Session::has("openid.authorization.response")) {
            Session::remove("openid.authorization.response");
        }
    }

    public function setUserAuthorizationResponse($auth_response)
    {
        Session::set("openid.authorization.response", $auth_response);
    }

    public function getUserByOpenId($openid)
    {
        $user = User::where('identifier', '=', $openid)->first();
        return $user;
    }

    public function getUserByUsername($username)
    {
        $member = Member::where('Email', '=', $username)->first();
        if(!is_null($member))
            return  User::where('external_identifier', '=', $member->ID)->first();
        return false;
    }

    public function getUserById($id)
    {
        return User::find($id);
    }

	// Authentication

	public function getUserAuthenticationResponse()
	{
		if (Session::has("openstackid.authentication.response")) {
			$value = Session::get("openstackid.authentication.response");
			return $value;
		}
		return IAuthService::AuthenticationResponse_None;
	}

	public function setUserAuthenticationResponse($auth_response)
	{
		Session::set("openstackid.authentication.response", $auth_response);
	}

	public function clearUserAuthenticationResponse()
	{
		if (Session::has("openstackid.authentication.response")) {
			Session::remove("openstackid.authentication.response");
		}
	}
}