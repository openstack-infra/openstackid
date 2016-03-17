<?php

namespace auth;

use Auth;
use Config;
use Crypt;
use Cookie;
use jwe\compression_algorithms\CompressionAlgorithms_Registry;
use jwe\compression_algorithms\CompressionAlgorithmsNames;
use Member;
use oauth2\models\IClient;
use oauth2\services\IPrincipalService;
use openid\model\IOpenIdUser;
use openid\services\IUserService;
use Session;
use utils\Base64UrlRepresentation;
use utils\services\IAuthService;
use utils\services\ICacheService;

/**
 * Class AuthService
 * @package auth
 */
final class AuthService implements IAuthService
{
    /**
     * @var IPrincipalService
     */
    private $principal_service;

    /**
     * @var IUserService
     */
    private $user_service;
    /**
     * @var ICacheService
     */
    private $cache_service;

    public function __construct
    (
        IPrincipalService $principal_service,
        IUserService $user_service,
        ICacheService $cache_service
    )
    {
        $this->principal_service = $principal_service;
        $this->user_service      = $user_service;
        $this->cache_service     = $cache_service;
    }

    /**
     * @return mixed
     */
    public function isUserLogged()
    {
        return Auth::check();
    }

    /**
     * @return IOpenIdUser
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
        $res = Auth::attempt(array('username' => $username, 'password' => $password), $remember_me);

        if ($res)
        {
            $this->principal_service->clear();
            $this->principal_service->register
            (
                $this->getCurrentUser()->getId(),
                time()
            );
        }

        return $res;
    }

    public function logout()
    {
        Auth::logout();
        $this->principal_service->clear();
        Cookie::queue('rps', null, $minutes = -2628000, $path = '/', $domain = null, $secure = false, $httpOnly = false);
    }

    /**
     * @return AuthorizationResponse_*
     */
    public function getUserAuthorizationResponse()
    {
        if (Session::has("openid.authorization.response"))
        {
            $value = Session::get("openid.authorization.response");

            return $value;
        }

        return IAuthService::AuthorizationResponse_None;
    }


    public function clearUserAuthorizationResponse()
    {
        if (Session::has("openid.authorization.response"))
        {
            Session::remove("openid.authorization.response");
            Session::save();
        }
    }

    public function setUserAuthorizationResponse($auth_response)
    {
        Session::set("openid.authorization.response", $auth_response);
        Session::save();
    }

    /**
     * @param string $openid
     * @return IOpenIdUser
     */
    public function getUserByOpenId($openid)
    {
        $user = User::where('identifier', '=', $openid)->first();

        return $user;
    }

    /**
     * @param string $username
     * @return bool|IOpenIdUser
     */
    public function getUserByUsername($username)
    {
        $member = Member::where('Email', '=', $username)->first();
        if (!is_null($member))
        {
            $user = User::where('external_identifier', '=', $member->ID)->first();

            if(!$user)
            {
                $user = $this->user_service->buildUser($member);
            }

            return $user;
        }

        return false;
    }

    /**
     * @param int $id
     * @return IOpenIdUsergetUserAuthorizationResponse
     */
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
        Session::save();
    }

    public function clearUserAuthenticationResponse()
    {
        if (Session::has("openstackid.authentication.response"))
        {
            Session::remove("openstackid.authentication.response");
            Session::save();
        }
    }

    /**
     * @param int $user_id
     * @return string
     */
    public function unwrapUserId($user_id)
    {
        $user = $this->getUserByExternalId($user_id);
        if(!is_null($user))
            return $user_id;

        $unwrapped_name = $this->decrypt($user_id);
        $parts          = explode(':', $unwrapped_name);
        return intval($parts[1]);
    }

    /**
     * @param int $user_id
     * @param IClient $client
     * @return string
     */
    public function wrapUserId($user_id, IClient $client)
    {
       if($client->getSubjectType() === IClient::SubjectType_Public)
           return $user_id;
       else
       {
           $wrapped_name = sprintf('%s:%s', $client->getClientId(), $user_id);
           return $this->encrypt($wrapped_name);
       }
    }

    /**
     * @param string $value
     * @return String
     */
    private function encrypt($value)
    {
        return base64_encode(Crypt::encrypt($value));
    }

    /**
     * @param string $value
     * @return String
     */
    private function decrypt($value)
    {
        $value = base64_decode($value);
        return Crypt::decrypt($value);
    }

    /**
     * @param int $external_id
     * @return IOpenIdUser
     */
    public function getUserByExternalId($external_id)
    {
        $member = Member::where('ID', '=', $external_id)->first();
        if (!is_null($member))
        {
            $user = User::where('external_identifier', '=', $member->ID)->first();

            if(!$user)
            {
                $user = $this->user_service->buildUser($member);
            }

            return $user;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
       return Session::getId();
    }

    /**
     * @param $client_id
     * @return void
     */
    public function registerRPLogin($client_id)
    {
        $rps  = Cookie::get('rps');
        $zlib = CompressionAlgorithms_Registry::getInstance()->get(CompressionAlgorithmsNames::ZLib);

        if(!empty($rps))
        {
            $rps = $this->decrypt($rps);
            $rps = $zlib->uncompress($rps);
            $rps .= '|';
        }

        if(!str_contains($rps, $client_id))
            $rps .= $client_id;

        $rps  = $zlib->compress($rps);
        $rps  = $this->encrypt($rps);

        Cookie::queue('rps', $rps, $minutes = 2628000, $path = '/', $domain = null, $secure = false, $httpOnly = false);
    }

    /**
     * @return string[]
     */
    public function getLoggedRPs()
    {
        $rps  = Cookie::get('rps');
        $zlib = CompressionAlgorithms_Registry::getInstance()->get(CompressionAlgorithmsNames::ZLib);

        if(!empty($rps))
        {
            $rps = $this->decrypt($rps);
            $rps = $zlib->uncompress($rps);
            return explode('|', $rps);
        }
        return null;
    }

    /**
     * @param string $jti
     * @throws \Exception
     */
    public function reloadSession($jti)
    {
        $session_id = $this->cache_service->getSingleValue($jti);
        if(empty($session_id)) throw new \Exception('session not found!');
        Session::setId(Crypt::decrypt($session_id));
        Session::start();
        if(!Auth::check())
        {
            $user_id      = $this->principal_service->get()->getUserId();
            $user         = $this->getUserById($user_id);
            if(is_null($user)) throw new \Exception('user not found!');
            Auth::login($user);
        }
    }
}