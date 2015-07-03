<?php

namespace oauth2\models;

use utils\IPHelper;
use Zend\Math\Rand;
use oauth2\OAuth2Protocol;
/**
 * Class AuthorizationCode
 * http://tools.ietf.org/html/rfc6749#section-1.3.1
 * @package oauth2\models
 */
class AuthorizationCode extends Token
{

    private $redirect_uri;
    private $access_type;
    private $approval_prompt;
    private $has_previous_user_consent;
    /**
     * @var string
     */
    private $state;
    /**
     * @var string
     */
    private $nonce;

    /**
     * @var string
     */
    private $response_type;

    /**
     * @var bool
     */
    private $requested_auth_time;

    /**
     * @var int
     */
    private $auth_time;

    public function __construct()
    {
        parent::__construct(64);
    }

    /**
     * @param $user_id
     * @param $client_id
     * @param $scope
     * @param string $audience
     * @param null $redirect_uri
     * @param string $access_type
     * @param string $approval_prompt
     * @param bool $has_previous_user_consent
     * @param int $lifetime
     * @param string|null $state
     * @param string|null $nonce
     * @param string|null $response_type
     * @param $requested_auth_time
     * @param $auth_time
     * @return AuthorizationCode
     */
    public static function create(
        $user_id,
        $client_id,
        $scope,
        $audience                  = '',
        $redirect_uri              = null,
        $access_type               = OAuth2Protocol::OAuth2Protocol_AccessType_Online,
        $approval_prompt           = OAuth2Protocol::OAuth2Protocol_Approval_Prompt_Auto,
        $has_previous_user_consent = false,
        $lifetime                  = 600,
        $state                     = null,
        $nonce                     = null,
        $response_type             = null,
        $requested_auth_time       = false,
        $auth_time                 = -1
    ) {
        $instance = new self();
        $instance->scope        = $scope;
        $instance->user_id      = $user_id;
        $instance->redirect_uri = $redirect_uri;
        $instance->client_id    = $client_id;
        $instance->lifetime     = intval($lifetime);
        $instance->audience     = $audience;
        $instance->is_hashed    = false;
        $instance->from_ip      = IPHelper::getUserIp();
        $instance->access_type  = $access_type;
        $instance->approval_prompt = $approval_prompt;
        $instance->has_previous_user_consent = $has_previous_user_consent;
        $instance->state = $state;
        $instance->nonce = $nonce;
        $instance->response_type = $response_type;
        $instance->requested_auth_time = $requested_auth_time;
        $instance->auth_time = $auth_time;

        return $instance;
    }

    /**
     * @param $value
     * @param $user_id
     * @param $client_id
     * @param $scope
     * @param string $audience
     * @param null $redirect_uri
     * @param null $issued
     * @param int $lifetime
     * @param string $from_ip
     * @param string $access_type
     * @param string $approval_prompt
     * @param bool $has_previous_user_consent
     * @param string|null $state
     * @param string|null $nonce
     * @param string|null $response_type
     * @param $requested_auth_time
     * @param $auth_time
     * @param bool $is_hashed
     * @return AuthorizationCode
     */
    public static function load
    (
        $value,
        $user_id,
        $client_id,
        $scope,$audience = '',
        $redirect_uri = null,
        $issued = null,
        $lifetime = 600,
        $from_ip = '127.0.0.1',
        $access_type = OAuth2Protocol::OAuth2Protocol_AccessType_Online,
        $approval_prompt = OAuth2Protocol::OAuth2Protocol_Approval_Prompt_Auto,
        $has_previous_user_consent = false,
        $state,
        $nonce,
        $response_type,
        $requested_auth_time       = false,
        $auth_time                 = -1,
        $is_hashed = false
    )
    {
        $instance = new self();
        $instance->value           = $value;
        $instance->user_id         = $user_id;
        $instance->scope           = $scope;
        $instance->redirect_uri    = $redirect_uri;
        $instance->client_id       = $client_id;
        $instance->audience        = $audience;
        $instance->issued          = $issued;
        $instance->lifetime        = intval($lifetime);
        $instance->from_ip         = $from_ip;
        $instance->is_hashed       = $is_hashed;
        $instance->access_type     = $access_type;
        $instance->approval_prompt = $approval_prompt;
        $instance->has_previous_user_consent = $has_previous_user_consent;
        $instance->state = $state;
        $instance->nonce = $nonce;
        $instance->response_type = $response_type;
        $instance->requested_auth_time = $requested_auth_time;
        $instance->auth_time = $auth_time;

        return $instance;
    }

    /**
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirect_uri;
    }

    /**
     * @return string
     */
    public function getAccessType()
    {
        return $this->access_type;
    }

    /**
     * @return string
     */
    public function getApprovalPrompt()
    {
        return $this->approval_prompt;
    }

    /**
     * @return string
     */
    public function getHasPreviousUserConsent()
    {
        return $this->has_previous_user_consent;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getNonce()
    {
        return $this->nonce;
    }

    /**
     * @return string
     */
    public function getResponseType()
    {
        return $this->response_type;
    }

    /**
     * @return bool
     */
    public function isAuthTimeRequested()
    {
        $res = $this->requested_auth_time;
        if (!is_string($res)) return (bool) $res;
        switch (strtolower($res)) {
            case '1':
            case 'true':
            case 'on':
            case 'yes':
            case 'y':
                return true;
        }
        return false;
    }

    /**
     * @return int
     */
    public function getAuthTime()
    {
        return $this->auth_time;
    }

    public function toJSON()
    {
        return '{}';
    }

    public function fromJSON($json)
    {
    }
}