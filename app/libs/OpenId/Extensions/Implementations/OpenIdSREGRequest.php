<?php namespace OpenId\Extensions\Implementations;
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
use Exception;
use OpenId\OpenIdMessage;
use OpenId\Requests\OpenIdRequest;
use OpenId\Exceptions\InvalidOpenIdMessageException;
use Utils\Http\HttpMessage;
/**
 * Class OpenIdSREGRequest
 * Implements @see http://openid.net/specs/openid-simple-registration-extension-1_0.html
 * @package OpenId\Extensions\Implementations
 */
class OpenIdSREGRequest extends OpenIdRequest
{
    /**
     * @var array
     */
    private $attributes;
    /**
     * @var array
     */
    private $optional_attributes;
    /**
     * @var string
     */
    private $policy_url;

    /**
     * OpenIdSREGRequest constructor.
     * @param OpenIdMessage $message
     */
    public function __construct(OpenIdMessage $message)
    {
        parent::__construct($message);
        $this->attributes          = array();
        $this->optional_attributes = array();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isValid()
    {
        try {
            //check identifier
            if (isset($this->message[OpenIdSREGExtension::paramNamespace(HttpMessage::PHP_REQUEST_VAR_SEPARATOR)])
                && $this->message[OpenIdSREGExtension::paramNamespace(HttpMessage::PHP_REQUEST_VAR_SEPARATOR)] == OpenIdSREGExtension::NamespaceUrl
            ) {

                /*
                 * All of the following request fields are OPTIONAL, though at least one of "openid.sreg.required"
                 * or "openid.sreg.optional" MUST be specified in the request.
                 * openid.sreg.required:
                 * Comma-separated list of field names which, if absent from the response, will prevent the Consumer f
                 * rom completing the registration without End User interation. The field names are those that are
                 * specified in the Response Format, with the "openid.sreg." prefix removed.
                 * openid.sreg.optional:
                 * Comma-separated list of field names Fields that will be used by the Consumer, but whose absence will
                 * not prevent the registration from completing. The field names are those that are specified in the
                 * Response Format, with the "openid.sreg." prefix removed.
                 * openid.sreg.policy_url:
                 * A URL which the Consumer provides to give the End User a place to read about the how the profile data
                 * will be used. The Identity Provider SHOULD display this URL to the End User if it is given.
                 */

                //check required fields

                if (
                    !isset($this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Required, HttpMessage::PHP_REQUEST_VAR_SEPARATOR)]) &&
                    !isset($this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Optional, HttpMessage::PHP_REQUEST_VAR_SEPARATOR)])
                )
                    throw new InvalidOpenIdMessageException("SREG: at least one of \"openid.sreg.required\" or \"openid.sreg.optional\" MUST be specified in the request.");

                //get required attributes
                if (isset($this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Required, HttpMessage::PHP_REQUEST_VAR_SEPARATOR)])) {
                    $attributes = $this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Required, HttpMessage::PHP_REQUEST_VAR_SEPARATOR)];
                    $attributes = explode(",", $attributes);

                    foreach ($attributes as $attr) {
                        $attr = trim($attr);
                        if (!isset(OpenIdSREGExtension::$available_properties[$attr]))
                            continue;
                        $this->attributes[$attr] = $attr;
                    }
                }

                //get optional attributes
                if (isset($this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Optional, HttpMessage::PHP_REQUEST_VAR_SEPARATOR)])) {
                    $opt_attributes = $this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Optional, HttpMessage::PHP_REQUEST_VAR_SEPARATOR)];
                    $opt_attributes = explode(",", $opt_attributes);
                    foreach ($opt_attributes as $opt_attr) {
                        $opt_attr = trim($opt_attr);
                        if (!isset(OpenIdSREGExtension::$available_properties[$opt_attr]))
                            continue;
                        if (isset($this->attributes[$opt_attr]))
                            throw new InvalidOpenIdMessageException(sprintf("SREG: optional attribute %s is already set as required one!", $opt_attr));
                        $this->optional_attributes[$opt_attr] = $opt_attr;
                    }
                }

                //check policy url..
                if (isset($this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::PolicyUrl, HttpMessage::PHP_REQUEST_VAR_SEPARATOR)])) {
                    $this->policy_url = $this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::PolicyUrl, HttpMessage::PHP_REQUEST_VAR_SEPARATOR)];
                }
                return true;
            }
        } catch (Exception $ex) {
            throw $ex;
        }
        return false;
    }

    /**
     * @return array
     */
    public function getRequiredAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return array
     */
    public function getOptionalAttributes()
    {
        return $this->optional_attributes;
    }

    /**
     * @return string
     */
    public function getPolicyUrl()
    {
        return $this->policy_url;
    }
}