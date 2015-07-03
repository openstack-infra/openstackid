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

use oauth2\exceptions\InvalidOAuth2Request;
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

    public static $optional_params = array
    (
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
     * @return bool
     */
    public function offlineAccessRequested()
    {
        return str_contains($this->getScope(), OAuth2Protocol::OfflineAccess_Scope);
    }

    /**
     * @param OAuth2AuthorizationRequest $auth_request
     */
    public function __construct(OAuth2AuthorizationRequest $auth_request)
    {
        parent::__construct($auth_request->getMessage());
    }

    /**
     * http://openid.net/specs/oauth-v2-multiple-response-types-1_0.html#ResponseModes
     * The Response Mode request parameter response_mode informs the Authorization Server of the mechanism to be used
     * for returning Authorization Response parameters from the Authorization Endpoint
     */
    public function getResponseMode()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_ResponseMode);
    }

    /**
     * Validates current request
     * @return bool
     */
    public function isValid()
    {
        $res = parent::isValid();
        if(!$res) throw new InvalidOAuth2Request('invalid parent request!');

        if($res)
        {
            if(!str_contains($this->getScope(), OAuth2Protocol::OpenIdConnect_Scope))
                throw new InvalidOAuth2Request('missing openid scope!');

            $display = $this->getDisplay();

            if(!empty($display) && !in_array($display, OAuth2Protocol::$valid_display_values))
                throw new InvalidOAuth2Request('not valid display value');

            $prompt = $this->getPrompt();

            if(!empty($prompt) && is_array($prompt))
            {
                // if this parameter contains none with any other value, an error is returned.
                if(in_array(OAuth2Protocol::OAuth2Protocol_Prompt_None, $prompt) && count($prompt) > 1)
                    throw new InvalidOAuth2Request('not valid prompt!');

                foreach($prompt as $p)
                {
                    if(!in_array($p, OAuth2Protocol::$valid_prompt_values))
                        throw new InvalidOAuth2Request('not valid prompt!');
                }
            }

            $response_mode = $this->getResponseMode();

            if(!empty($response_mode))
            {
                if(!in_array($response_mode, OAuth2Protocol::$valid_response_modes)) throw new InvalidOAuth2Request('invalid response_mode!');

                $default_response_mode = OAuth2Protocol::getDefaultResponseMode($this->getResponseType(false));

                if($default_response_mode === $response_mode) throw new InvalidOAuth2Request('invalid response_mode!');
            }

            // http://openid.net/specs/openid-connect-core-1_0.html#OfflineAccess
            // MUST ensure that the prompt parameter contains consent unless other conditions for processing the request
            // permitting offline access to the requested resources are in place
            if($this->offlineAccessRequested() && empty($prompt))
                throw new InvalidOAuth2Request('invalid offline access!');

            if($this->offlineAccessRequested() && !in_array(OAuth2Protocol::OAuth2Protocol_Prompt_Consent, $prompt))
                throw new InvalidOAuth2Request('invalid offline access!');
        }

        return $res;
    }

    /**
     * @param string $param_name
     * @return bool
     */
    public function isProcessedParam($param_name){
        $res = explode(' ', $this->getParam('processed_params'));
        return in_array($param_name, $res);
    }

    /**
     * @param string $param_name
     * @return $this
     */
    public function markParamAsProcessed($param_name)
    {
        $res = $this->getParam('processed_params');
        if(!empty($res))
        {
            $res = $res .' ';
        }
        $res = $res .$param_name;
        $this->setParam('processed_params', $res);
        return $this;
    }
}