<?php namespace OpenId\Responses;
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
use OpenId\OpenIdProtocol;
/**
 * Class OpenIdIndirectResponse
 * @package OpenId\Responses
 */
class OpenIdIndirectResponse extends OpenIdResponse
{

    const IndirectResponseContentType = "application/x-www-form-urlencoded";
    const OpenIdIndirectResponse      = "OpenIdIndirectResponse";

    public function __construct()
    {
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct(self::HttpOkResponse, self::IndirectResponseContentType);
        /*
         * This particular value MUST be present for the response to be a valid OpenID 2.0
         * response. Future versions of the specification may define different values in order
         * to allow message recipients to properly interpret the request.
         */
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)] = OpenIdProtocol::OpenID2MessageType;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        $url_encoded_format = "";
        if ($this->container !== null) {
            ksort($this->container);
            foreach ($this->container as $key => $value) {
                if (is_array($value)) {
                    list($key, $value) = array($value[0], $value[1]);
                }
                $value = urlencode($value);
                $url_encoded_format .= "$key=$value&";
            }
            $url_encoded_format = rtrim($url_encoded_format, '&');
        }
        return $url_encoded_format;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return self::OpenIdIndirectResponse;
    }

    /**
     * @param string $return_to
     * @return $this
     */
    public function setReturnTo($return_to)
    {
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)] = $return_to;
        return $this;
    }

    /**
     * @return string
     */
    public function getReturnTo()
    {
        return $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)];
    }
}