<?php namespace Auth;
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
use Auth\Repositories\IMemberRepository;
use Auth\Repositories\IUserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use OAuth2\Models\IClient;
use OAuth2\Services\IPrincipalService;
use OpenId\Models\IOpenIdUser;
use OpenId\Services\IUserService;
use Utils\Services\IAuthService;
use Utils\Services\ICacheService;
use jwe\compression_algorithms\CompressionAlgorithms_Registry;
use jwe\compression_algorithms\CompressionAlgorithmsNames;
use Exception;
/**
 * Class AuthService
 * @package Auth
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

    /**
     * @var IUserRepository
     */
    private $user_repository;

    /**
     * @var IMemberRepository
     */
    private $member_repository;

    /**
     * AuthService constructor.
     * @param IMemberRepository $member_repository
     * @param IUserRepository $user_repository
     * @param IPrincipalService $principal_service
     * @param IUserService $user_service
     * @param ICacheService $cache_service
     */
    public function __construct
    (
        IMemberRepository $member_repository,
        IUserRepository $user_repository,
        IPrincipalService $principal_service,
        IUserService $user_service,
        ICacheService $cache_service
    )
    {
        $this->member_repository = $member_repository;
        $this->user_repository   = $user_repository;
        $this->principal_service = $principal_service;
        $this->user_service      = $user_service;
        $this->cache_service     = $cache_service;
    }

    /**
     * @return bool
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
     * @param string $username
     * @param string $password
     * @param bool $remember_me
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
        return $this->user_repository->getByIdentifier($openid);
    }

    /**
     * @param string $username
     * @return bool|IOpenIdUser
     */
    public function getUserByUsername($username)
    {
        $member =  $this->member_repository->getByEmail($username);

        if (!is_null($member))
        {
            $user = $this->user_repository->getByExternalId($member->ID);

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
        return $this->user_repository->get($id);
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

        $wrapped_name = sprintf('%s:%s', $client->getClientId(), $user_id);
        return $this->encrypt($wrapped_name);
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
        $member = $this->member_repository->get($external_id);

        if (!is_null($member))
        {
            $user = $this->user_repository->getByExternalId($member->ID);

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
     * @throws Exception
     */
    public function reloadSession($jti)
    {
        $session_id = $this->cache_service->getSingleValue($jti);
        if(empty($session_id)) throw new Exception('session not found!');
        Session::setId(Crypt::decrypt($session_id));
        Session::start();
        if(!Auth::check())
        {
            $user_id      = $this->principal_service->get()->getUserId();
            $user         = $this->getUserById($user_id);
            if(is_null($user)) throw new Exception('user not found!');
            Auth::login($user);
        }
    }
}