<?php

use Illuminate\Validation\Validator;
use Symfony\Component\Translation\TranslatorInterface;
use \Crypt_RSA;
use jwk\JSONWebKeyPublicKeyUseValues;
use jwk\JSONWebKeyTypes;
use oauth2\OAuth2Protocol;
use oauth2\models\IClient;

/**
 * Class CustomValidator
 * Custom validation methods
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

    public function validateBoolean($attribute, $value, $parameters)
    {
        if (is_bool($value)) {
            return true;
        }
        if (is_int($value)) {
            return true;
        }

        return strtoupper(trim($value)) == 'TRUE' || strtoupper(trim($value)) == 'FALSE' || strtoupper(trim($value)) == '1' || strtoupper(trim($value)) == '0';
    }

    public function validateText($attribute, $value, $parameters)
    {
        $value = trim($value);

        return preg_match("%^[a-zA-Z0-9\s\-\.\,\/\_]+$%i", $value) == 1;
    }

    public function validateHttpmethod($attribute, $value, $parameters)
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

    public function validateRoute($attribute, $value, $parameters)
    {
        return true;
    }

    public function validateScopename($attribute, $value, $parameters)
    {
        $value = trim($value);

        return preg_match("/^[a-zA-Z0-9\-\.\,\:\_\/]+$/", $value) == 1;
    }

    public function validateHost($attribute, $value, $parameters)
    {
        return filter_var(gethostbyname($value), FILTER_VALIDATE_IP) ? true : false;
    }

    public function validateApplicationtype($attribute, $value, $parameters)
    {

        if (!is_string($value)) {
            return false;
        }

        $value = trim($value);

        return in_array($value, Client::$valid_app_types);
    }

    public function validateSslurl($attribute, $value, $parameters)
    {
        return preg_match(";^https:\/\/([\w@][\w.:@]+)\/?[\w\.?=%&=\-@/$,]*$;i", $value) == 1;
    }

    public function validateFreeText($attribute, $value, $parameters)
    {
        return preg_match('|^[a-z\-@_.,()\'"\s\:\/]+$|i', $value) == 1;
    }


    public function validateSslorigin($attribute, $value, $parameters)
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

    public function validateEmailSet($attribute, $value, $parameters)
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

    public function validateUrlSet($attribute, $value, $parameters)
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

    public function validateTokenEndpointAuthMethod($attribute, $value, $parameters)
    {
        return in_array($value,OAuth2Protocol::$token_endpoint_auth_methods);
    }

    public function validateSigningAlg($attribute, $value, $parameters)
    {
        return in_array($value,OAuth2Protocol::$supported_signing_algorithms);
    }

    public function validateSubjectType($attribute, $value, $parameters)
    {
       return in_array($value, Client::$valid_subject_types);
    }

    public function validateEncryptedAlg($attribute, $value, $parameters)
    {
        return in_array($value,OAuth2Protocol::$supported_key_management_algorithms);
    }

    public function validateEncryptedEnc($attribute, $value, $parameters)
    {
        return in_array($value,OAuth2Protocol::$supported_content_encryption_algorithms);
    }

    public function validatePublicKeyPem($attribute, $value, $parameters)
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

    public function validatePublicKeyPemLength($attribute, $value, $parameters)
    {
        $rsa    = new Crypt_RSA;
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
            $res = $app_type === IClient::ApplicationType_Native ? $this->validateUrl($attribute, $url, $parameters) : $this->validateSslurl($attribute, $url, $parameters);
            if (!$res) {
                break;
            }
        }

        return $res;
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
} 