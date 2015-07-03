<?php
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

namespace oauth2\models;

/**
 * Class SecurityContext
 * @package oauth2\models
 */
final class SecurityContext
{
    /**
     * @var int
     */
    private $requested_user_id;

    /**
     * @var bool
     */
    private $requested_auth_time;

    /**
     * @var bool
     */
    private $login_prompt_proccessed;

    /**
     * @var bool
     */
    private $consent_prompt_proccessed;

    /**
     * @return bool
     */
    public function isLoginPromptProccessed()
    {
        return $this->login_prompt_proccessed;
    }

    public function setLoginPromptProccessed($login_prompt_proccessed)
    {
        $this->login_prompt_proccessed = $login_prompt_proccessed;
        return $this;
    }

    /**
     * @return bool
     */
    public function isConsentPromptProccessed()
    {
        return $this->consent_prompt_proccessed;
    }

    public function setConsentPromptProccessed($consent_prompt_proccessed)
    {
        $this->consent_prompt_proccessed = $consent_prompt_proccessed;
        return $this;
    }

    /**
     * @return int
     */
    public function getRequestedUserId()
    {
        return $this->requested_user_id;
    }

    /**
     * @param int $requested_user_id
     * @return $this
     */
    public function setRequestedUserId($requested_user_id)
    {
        $this->requested_user_id = $requested_user_id;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthTimeRequired()
    {
        return $this->requested_auth_time;
    }

    /**
     * @param bool $requested_auth_time
     * @return $this
     */
    public function setAuthTimeRequired($requested_auth_time)
    {
        $this->requested_auth_time = $requested_auth_time;
        return $this;
    }


    /**
     * @return array
     */
    public function getState()
    {
        return array
        (
            $this->requested_user_id,
            $this->requested_auth_time,
            $this->login_prompt_proccessed,
            $this->consent_prompt_proccessed
        );
    }

    /**
     * @param array $state
     * @return $this
     */
    public function setState(array $state)
    {
        $this->requested_user_id         = $state[0];
        $this->requested_auth_time       = $state[1];
        $this->login_prompt_proccessed   = is_null($state[2]) ? false : $state[2];
        $this->consent_prompt_proccessed = is_null($state[3]) ? false : $state[3];
        return $this;
    }
}