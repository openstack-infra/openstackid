<?php namespace Validators;
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
use Illuminate\Validation\Validator;
use Models\OAuth2\Client;
use Symfony\Component\Translation\TranslatorInterface;
use jwk\JSONWebKeyPublicKeyUseValues;
use jwk\JSONWebKeyTypes;
use OAuth2\OAuth2Protocol;
use OAuth2\Models\IClient;
use Utils\Services\IAuthService;
use Crypt_RSA;

/**
 * Class CustomValidator
 * @package Validators
 */
class CustomValidator extends Validator
{

    protected $implicitRules = array(
        'Required',
        'RequiredWith',
        'RequiredWithout',
        'RequiredIf',
        'Accepted',
        'RequiredWithoutField'
    );

    public function __construct(TranslatorInterface $translator, $data, $rules, $messages = array())
    {
        parent::__construct($translator, $data, $rules, $messages);
        $this->isImplicit('fail');
    }

    public function validateBoolean($attribute, $value)
    {
        if (is_bool($value)) {
            return true;
        }
        if (is_int($value)) {
            return true;
        }

        return strtoupper(trim($value)) == 'TRUE' || strtoupper(trim($value)) == 'FALSE' || strtoupper(trim($value)) == '1' || strtoupper(trim($value)) == '0';
    }

    public function validateText($attribute, $value)
    {
        $value = trim($value);

        return preg_match("%^[a-zA-Z0-9\s\-\.\,\/\_]+$%i", $value) == 1;
    }

    public function validateHttpmethod($attribute, $value)
    {
        $value = strtoupper(trim($value));
        //'GET', 'HEAD','POST','PUT','DELETE','TRACE','CONNECT','OPTIONS'
        $allowed_http_verbs = array(
            'GET' => 'GET',
            'HEAD' => 'HEAD',
            'POST' => 'POST',
            'PUT' => 'PUT',
            'DELETE' => 'DELETE',
            'TRACE' => 'TRACE',
            'CONNECT' => 'CONNECT',
            'OPTIONS' => 'OPTIONS',
        );

        return array_key_exists($value, $allowed_http_verbs);
    }

    public function validateRoute($attribute, $value)
    {
        return true;
    }

    public function validateScopename($attribute, $value)
    {
        $value = trim($value);

        return preg_match("/^[a-zA-Z0-9\-\.\,\:\_\/]+$/", $value) == 1;
    }

    public function validateHost($attribute, $value)
    {
        return filter_var(gethostbyname($value), FILTER_VALIDATE_IP) ? true : false;
    }

    public function validateApplicationtype($attribute, $value)
    {

        if (!is_string($value)) {
            return false;
        }

        $value = trim($value);

        return in_array($value, Client::$valid_app_types);
    }

    public function validateSslurl($attribute, $value)
    {
        return preg_match(";^https:\/\/([\w@][\w.:@]+)\/?[\w\.?=%&=\-@/$,]*$;i", $value) == 1;
    }

    public function validateFreeText($attribute, $value)
    {
        return preg_match('|^[a-z0-9A-Z\-@_.,()\'"\s\:\/]+$|i', $value) == 1;
    }

    public function validateSslorigin($attribute, $value)
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $parts = @parse_url($value);

            if ($parts == false) {
                return false;
            }

            if ($parts['scheme'] != 'https') {
                return false;
            }

            if (isset($parts['query'])) {
                return false;
            }

            if (isset($parts['fragment'])) {
                return false;
            }

            if (isset($parts['path'])) {
                return false;
            }

            if (isset($parts['user'])) {
                return false;
            }

            if (isset($parts['pass'])) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function validateEmailSet($attribute, $value)
    {
        $emails = explode(',', $value);
        $res = true;
        foreach ($emails as $email) {
            $res = $this->validateEmail($attribute, $email);
            if (!$res) {
                break;
            }
        }

        return $res;
    }

    public function validateUrlSet($attribute, $value)
    {
        $urls = explode(',', $value);
        $res = true;
        foreach ($urls as $url) {
            $res = $this->validateUrl($attribute, $url);
            if (!$res) {
                break;
            }
        }

        return $res;
    }

    public function validateTokenEndpointAuthMethod($attribute, $value)
    {
        return in_array($value,OAuth2Protocol::$token_endpoint_auth_methods);
    }

    public function validateSigningAlg($attribute, $value)
    {
        return in_array($value,OAuth2Protocol::$supported_signing_algorithms);
    }

    public function validateSubjectType($attribute, $value)
    {
       return in_array($value, Client::$valid_subject_types);
    }

    public function validateEncryptedAlg($attribute, $value)
    {
        return in_array($value,OAuth2Protocol::$supported_key_management_algorithms);
    }

    public function validateEncryptedEnc($attribute, $value)
    {
        return in_array($value,OAuth2Protocol::$supported_content_encryption_algorithms);
    }

    public function validatePublicKeyPem($attribute, $value)
    {
        $res1 = strpos($value,'-----BEGIN PUBLIC KEY-----');
        $res2 = strpos($value,'-----BEGIN RSA PUBLIC KEY-----');
        $res3 = strpos($value,'-----END PUBLIC KEY-----');
        $res4 = strpos($value,'-----END RSA PUBLIC KEY-----');

        $PKCS8 = $res1 !== false && $res3 !== false;
        $PKCS1 = $res2 !== false && $res4 !== false;

        $rsa    = new Crypt_RSA;
        $parsed = $rsa->loadKey($value);

        return ($PKCS8 || $PKCS1) && $parsed;
    }

    public function validatePublicKeyPemLength($attribute, $value)
    {
        $rsa    = new Crypt_RSA();
        $parsed = $rsa->loadKey($value);

        return $parsed && $rsa->getSize() > 1024;
    }

    public function validatePrivateKeyPem($attribute, $value, $parameters)
    {
        $res1 = strpos($value,'-----BEGIN PRIVATE KEY-----');
        $res2 = strpos($value,'-----BEGIN RSA PRIVATE KEY-----');
        $res3 = strpos($value,'-----END PRIVATE KEY-----');
        $res4 = strpos($value,'-----END RSA PRIVATE KEY-----');

        $PKCS8 = $res1 !== false && $res3 !== false;
        $PKCS1 = $res2 !== false && $res4 !== false;

        $encrypted      = strpos($value,'ENCRYPTED') !== false ;
        $password_param = $parameters[0];
        $rsa            = new Crypt_RSA;
        if(isset($this->data[$password_param]) && $encrypted){
            $rsa->setPassword($this->data[$password_param]);
        }

        $parsed         = $rsa->loadKey($value);

        return ($PKCS8 || $PKCS1) && $parsed;
    }

    public function validatePrivateKeyPemLength($attribute, $value, $parameters)
    {

        $encrypted      = strpos($value,'ENCRYPTED') !== false ;
        $password_param = $parameters[0];
        $rsa            = new Crypt_RSA;
        if(isset($this->data[$password_param]) && $encrypted){
            $rsa->setPassword($this->data[$password_param]);
        }

        $parsed         = $rsa->loadKey($value);

        return $parsed && $rsa->getSize() >= 2048;
    }

    public function validatePublicKeyUsage($attribute, $value, $parameters)
    {
        return in_array($value, JSONWebKeyPublicKeyUseValues::$valid_uses);
    }

    public function validatePublicKeyType($attribute, $value, $parameters)
    {
        return in_array($value, JSONWebKeyTypes::$valid_keys_set);
    }

    public function validatePrivateKeyPassword($attribute, $value, $parameters){
       $pem_param = $parameters[0];
       if(!isset($this->data[$pem_param])) return true;
       $pem_content = $this->data[$pem_param];
       $rsa    = new Crypt_RSA;
       $rsa->setPassword($value);
       $parsed = $rsa->loadKey($pem_content);
       return $parsed;
    }

    public function validateCustomUrlSet($attribute, $value, $parameters)
    {
        $app_type_param = $parameters[0];
        if(!isset($this->data[$app_type_param])) return true;
        $app_type = $this->data[$app_type_param];

        $urls = explode(',', $value);
        $res = true;
        foreach ($urls as $url) {
            $res = $app_type === IClient::ApplicationType_Native ? $this->validateCustomUrl($attribute, $url, $parameters): $this->validateSslurl($attribute, $url, $parameters);
            if (!$res) {
                break;
            }
        }

        return $res;
    }

    public function validateCustomUrl($attribute, $value, $paramenters){
        $uri = @parse_url($value);
        if (!isset($uri['scheme'])) {
           return false;
        }
        return true;
    }

    public function validateSslUrlSet($attribute, $value, $parameters)
    {

        $urls = explode(',', $value);
        $res = true;
        foreach ($urls as $url) {
            $res = $this->validateSslurl($attribute, $url, $parameters);
            if (!$res) {
                break;
            }
        }

        return $res;
    }

    public function validateKeyAlg($attribute, $value, $parameters)
    {
        $key_type_param = $parameters[0];
        $key_type       = $this->data[$key_type_param];

        if($key_type === JSONWebKeyPublicKeyUseValues::Signature)
        {
            return in_array($value, OAuth2Protocol::$supported_signing_algorithms);
        }
        else
        {
            return in_array($value, OAuth2Protocol::$supported_key_management_algorithms);
        }
    }

    public function validateOauth2TrustResponse($attribute, $value, $parameters){
        $valid_values = array
        (
            IAuthService::AuthorizationResponse_AllowOnce,
            IAuthService::AuthorizationResponse_DenyOnce,
            IAuthService::AuthorizationResponse_AllowForever,
            IAuthService::AuthorizationResponse_DenyForever,
        );
        if(is_array($value)) $value = $value[0];
        return in_array($value, $valid_values);
    }

    public function validateUserIds($attribute, $value, $parameters)
    {
        $user_ids = explode(',',$value);
        foreach($user_ids as $id)
        {
            if(!intval($id)) return false;
        }
        return true;
    }
} 