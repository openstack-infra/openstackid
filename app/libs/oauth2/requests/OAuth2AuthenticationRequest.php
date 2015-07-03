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

namespace oauth2\requests;

use oauth2\OAuth2Protocol;

/**
 * Class OAuth2AuthenticationRequest
 * @package oauth2\requests
 * http://openid.net/specs/openid-connect-core-1_0.html#AuthRequest
 * An Authentication Request is an OAuth 2.0 Authorization Request that requests that the End-User be authenticated by
 * the Authorization Server.
 */
class OAuth2AuthenticationRequest extends OAuth2AuthorizationRequest
{

    public static $optional_params = array(
        OAuth2Protocol::OAuth2Protocol_Nonce,
        OAuth2Protocol::OAuth2Protocol_Display,
        OAuth2Protocol::OAuth2Protocol_Prompt,
        OAuth2Protocol::OAuth2Protocol_MaxAge,
        OAuth2Protocol::OAuth2Protocol_UILocales,
        OAuth2Protocol::OAuth2Protocol_IDTokenHint,
        OAuth2Protocol::OAuth2Protocol_LoginHint,
        OAuth2Protocol::OAuth2Protocol_ACRValues,
    );

    /**
     * @return null|string
     */
    public function getNonce()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_Nonce);
    }

    /**
     * @return null|string
     */
    public function getDisplay()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_Display);
    }

    /**
     * @return string[]
     */
    public function getPrompt()
    {
        $prompt = $this->getParam(OAuth2Protocol::OAuth2Protocol_Prompt);
        if(!empty($prompt))
            return explode(' ', $prompt);
        return array();
    }

    /**
     * @return null|int
     */
    public function getMaxAge()
    {
        $value =  $this->getParam(OAuth2Protocol::OAuth2Protocol_MaxAge);
        if(!is_null($value))
            $value = intval($value);
        return $value;
    }

    /**
     * @return null|string
     */
    public function getUILocales()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_UILocales);
    }

    /**
     * @return null|string
     */
    public function getIdTokenHint()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_IDTokenHint);
    }

    /**
     * @return null|string
     */
    public function getLoginHint()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_LoginHint);
    }

    /**
     * @return null|string
     */
    public function getACRValues()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_ACRValues);
    }

    /**
     * @param OAuth2AuthorizationRequest $auth_request
     */
    public function __construct(OAuth2AuthorizationRequest $auth_request){
        parent::__construct($auth_request->getMessage());
    }

    /**
     * Validates current request
     * @return bool
     */
    public function isValid()
    {
        $res = parent::isValid();

        if($res)
        {
            if(!str_contains($this->getScope(), OAuth2Protocol::OpenIdConnect_Scope))
                return false;

            $display = $this->getDisplay();
            if(!empty($display) && !in_array($display, OAuth2Protocol::$valid_display_values))
                return false;

            $prompt = $this->getPrompt();
            if(!empty($prompt) && is_array($prompt))
            {
                // if this parameter contains none with any other value, an error is returned.
                if(in_array(OAuth2Protocol::OAuth2Protocol_Prompt_None, $prompt) && count($prompt) > 1)
                    return false;

                foreach($prompt as $p){
                    if(!in_array($p, OAuth2Protocol::$valid_prompt_values)) return false;
                }
            }
        }
        return $res;
    }
}