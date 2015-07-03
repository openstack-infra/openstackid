<?php

namespace auth;

use Auth;
use Config;
use Member;
use oauth2\models\IClient;
use oauth2\services\IPrincipalService;
use openid\model\IOpenIdUser;
use Session;
use utils\services\IAuthService;

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
     * @var \Crypt_AES()
     */
    private $aes;

    /**
     * @param IPrincipalService $principal_service
     */
    public function __construct(IPrincipalService $principal_service)
    {
        $this->principal_service = $principal_service;

        $this->aes = new \Crypt_AES();
        $this->aes->Crypt_Base(CRYPT_AES_MODE_CBC);

        $this->enc_key = Config::get('privatekey.EncryptionKey');
        if(empty($this->enc_key)) throw new \InvalidArgumentException('privatekey.EncryptionKey not set!');
        $this->iv      = Config::get('privatekey.IV');
        if(empty($this->iv)) throw new \InvalidArgumentException('privatekey.IV not set!');
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
                $this->getCurrentUser()->getAuthIdentifier(),
                time()
            );
        }

        return $res;
    }

    public function logout()
    {
        Auth::logout();
        $this->principal_service->clear();
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

    public function clearUserAuthorizationResponse()
    {
        if (Session::has("openid.authorization.response")) {
            Session::remove("openid.authorization.response");
        }
    }

    public function setUserAuthorizationResponse($auth_response)
    {
        Session::set("openid.authorization.response", $auth_response);
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
        if (!is_null($member)) {
            return User::where('external_identifier', '=', $member->ID)->first();
        }

        return false;
    }

    /**
     * @param int $id
     * @return IOpenIdUser
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
    }

    public function clearUserAuthenticationResponse()
    {
        if (Session::has("openstackid.authentication.response")) {
            Session::remove("openstackid.authentication.response");
        }
    }

    /**
     * @param int $user_id
     * @return string
     */
    public function unwrapUserId($user_id)
    {
        $user = $this->getUserByExternaldId($user_id);
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
        $this->aes->setKey($this->enc_key);
        $this->aes->setIV($this->iv);
        return base64_encode($this->aes->encrypt($value));
    }

    /**
     * @param string $value
     * @return String
     */
    private function decrypt($value)
    {
        $value = base64_decode($value);
        $this->aes->setKey($this->enc_key);
        $this->aes->setIV($this->iv);
        return $this->aes->decrypt($value);
    }

    /**
     * @param int $external_id
     * @return IOpenIdUser
     */
    public function getUserByExternaldId($external_id)
    {
        return User::where('external_identifier', '=', $external_id)->first();
    }
}