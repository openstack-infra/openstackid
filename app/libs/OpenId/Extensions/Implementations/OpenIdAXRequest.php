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

use OpenId\Exceptions\InvalidOpenIdMessageException;
use OpenId\Helpers\OpenIdErrorMessages;
use OpenId\OpenIdMessage;
use OpenId\Requests\OpenIdRequest;
use Utils\Http\HttpMessage;

/**
 * Class OpenIdAXRequest
 * Implements http://openid.net/specs/openid-attribute-exchange-1_0.html
 * @package OpenId\Extensions\Implementations
 */
class OpenIdAXRequest extends OpenIdRequest
{
    /**
     * @var array
     */
    private $attributes;

    /**
     * OpenIdAXRequest constructor.
     * @param OpenIdMessage $message
     */
    public function __construct(OpenIdMessage $message)
    {
        parent::__construct($message);
        $this->attributes = array();
    }

    /**
     * @return bool
     * @throws InvalidOpenIdMessageException
     */
    public function isValid()
    {

        //check identifier
        if (isset($this->message[OpenIdAXExtension::paramNamespace(HttpMessage::PHP_REQUEST_VAR_SEPARATOR)])
            && $this->message[OpenIdAXExtension::paramNamespace(HttpMessage::PHP_REQUEST_VAR_SEPARATOR)] == OpenIdAXExtension::NamespaceUrl
        ) {

            //check required fields

            if (!isset($this->message[OpenIdAXExtension::param(OpenIdAXExtension::Mode, HttpMessage::PHP_REQUEST_VAR_SEPARATOR)])
                || $this->message[OpenIdAXExtension::param(OpenIdAXExtension::Mode, HttpMessage::PHP_REQUEST_VAR_SEPARATOR)] != OpenIdAXExtension::FetchRequest
            )
                throw new InvalidOpenIdMessageException(OpenIdErrorMessages::AXInvalidModeMessage);

            if (!isset($this->message[OpenIdAXExtension::param(OpenIdAXExtension::RequiredAttributes, HttpMessage::PHP_REQUEST_VAR_SEPARATOR)]))
                throw new InvalidOpenIdMessageException(OpenIdErrorMessages::AXInvalidRequiredAttributesMessage);

            //get attributes
            $attributes = $this->message[OpenIdAXExtension::param(OpenIdAXExtension::RequiredAttributes, HttpMessage::PHP_REQUEST_VAR_SEPARATOR)];
            $attributes = explode(",", $attributes);

            foreach ($attributes as $attr) {
                $attr = trim($attr);
                if (!isset(OpenIdAXExtension::$available_properties[$attr]))
                    continue;

                $attr_ns = OpenIdAXExtension::param(OpenIdAXExtension::Type, HttpMessage::PHP_REQUEST_VAR_SEPARATOR) . HttpMessage::PHP_REQUEST_VAR_SEPARATOR . $attr;

                if (!isset($this->message[$attr_ns]))
                    throw new InvalidOpenIdMessageException(sprintf(OpenIdErrorMessages::AXInvalidNamespaceMessage,$attr_ns, $attr));

                $ns = $this->message[$attr_ns];

                if ($ns != OpenIdAXExtension::$available_properties[$attr])
                    throw new InvalidOpenIdMessageException(sprintf(OpenIdErrorMessages::AXInvalidNamespaceMessage, $ns, $attr));

                array_push($this->attributes, $attr);
            }
            return true;
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
}