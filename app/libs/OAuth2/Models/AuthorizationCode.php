<?php namespace OAuth2\Models;
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
use Utils\IPHelper;
use OAuth2\OAuth2Protocol;
/**
 * Class AuthorizationCode
 * http://tools.ietf.org/html/rfc6749#section-1.3.1
 * @package OAuth2\Models
 */
class AuthorizationCode extends Token
{
    /**
     * @var string
     */
    private $redirect_uri;

    /**
     * @var string
     */
    private $access_type;

    /**
     * @var string
     */
    private $approval_prompt;

    /**
     * @var bool
     */
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
     * @var string
     * prompt
     * OPTIONAL.
     * Space delimited, case sensitive list of ASCII string values that specifies whether the Authorization
     * Server prompts the End-User for reauthentication and consent. The defined values are:
     * none
     *      The Authorization Server MUST NOT display any authentication or consent user interface pages. An error is
     *      returned if an End-User is not already authenticated or the Client does not have pre-configured consent for
     *      the requested Claims or does not fulfill other conditions for processing the request. The error code will
     *      typically be login_required, interaction_required, or another code defined in Section 3.1.2.6. This can be
     *      used as a method to check for existing authentication and/or consent.
     * login
     *      The Authorization Server SHOULD prompt the End-User for reauthentication. If it cannot reauthenticate the
     *      End-User, it MUST return an error, typically login_required.
     * consent
     *      The Authorization Server SHOULD prompt the End-User for consent before returning information to the Client.
     *      If it cannot obtain consent, it MUST return an error, typically consent_required.
     * select_account
     *      The Authorization Server SHOULD prompt the End-User to select a user account. This enables an End-User who
     *      has multiple accounts at the Authorization Server to select amongst the multiple accounts that they might
     *      have current sessions for. If it cannot obtain an account selection choice made by the End-User, it MUST
     *      return an error, typically account_selection_required.
     *
     * The prompt parameter can be used by the Client to make sure that the End-User is still present for the current
     * session or to bring attention to the request. If this parameter contains none with any other value, an error is
     * returned.
     */
    private $prompt;

    /**
     * @var int
     */
    private $auth_time;

    const Length = 128;

    public function __construct()
    {
        parent::__construct(self::Length);
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
     * @param null|string $prompt
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
        $auth_time                 = -1,
        $prompt                    = null
    ) {
        $instance                            = new self();
        $instance->scope                     = $scope;
        $instance->user_id                   = $user_id;
        $instance->redirect_uri              = $redirect_uri;
        $instance->client_id                 = $client_id;
        $instance->lifetime                  = intval($lifetime);
        $instance->audience                  = $audience;
        $instance->is_hashed                 = false;
        $instance->from_ip                   = IPHelper::getUserIp();
        $instance->access_type               = $access_type;
        $instance->approval_prompt           = $approval_prompt;
        $instance->has_previous_user_consent = $has_previous_user_consent;
        $instance->state                     = $state;
        $instance->nonce                     = $nonce;
        $instance->response_type             = $response_type;
        $instance->requested_auth_time       = $requested_auth_time;
        $instance->auth_time                 = $auth_time;
        $instance->prompt                    = $prompt;

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
     * @param null|string $prompt
     * @param bool $is_hashed
     * @return AuthorizationCode
     */
    public static function load
    (
        $value,
        $user_id,
        $client_id,
        $scope,
        $audience                  = '',
        $redirect_uri              = null,
        $issued                    = null,
        $lifetime                  = 600,
        $from_ip                   = '127.0.0.1',
        $access_type               = OAuth2Protocol::OAuth2Protocol_AccessType_Online,
        $approval_prompt           = OAuth2Protocol::OAuth2Protocol_Approval_Prompt_Auto,
        $has_previous_user_consent = false,
        $state,
        $nonce,
        $response_type,
        $requested_auth_time       = false,
        $auth_time                 = -1,
        $prompt                    = null,
        $is_hashed                 = false
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
        $instance->prompt   = $prompt;
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
     * @return string
     */
    public function getPrompt()
    {
        return $this->prompt;
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

    /**
     * @return string
     */
    public function getType()
    {
        return 'auth_code';
    }
}