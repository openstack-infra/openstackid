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
use OpenId\Exceptions\InvalidKVFormat;
use OpenId\Helpers\OpenIdErrorMessages;
use OpenId\OpenIdProtocol;
use Utils\Http\HttpContentType;
/**
 * Class OpenIdDirectResponse
 * Implementation of 5.1.2. Direct Response
 * @package OpenId\Responses
 */
class OpenIdDirectResponse extends OpenIdResponse
{

    const OpenIdDirectResponse = "OpenIdDirectResponse";

    public function __construct()
    {
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct(self::HttpOkResponse, HttpContentType::Text);
        /*
         * This particular value MUST be present for the response to be a valid OpenID 2.0
         * response. Future versions of the specification may define different values in order
         * to allow message recipients to properly interpret the request.
         */
        $this["ns"] = OpenIdProtocol::OpenID2MessageType;
    }

    /**
     * Implementation of 4.1.1.  Key-Value Form Encoding
     * @return string
     * @throws InvalidKVFormat
     */
    public function getContent()
    {
        $kv_format = "";
        if ($this->container !== null) {
            ksort($this->container);
            foreach ($this->container as $key => $value) {
                if (is_array($value)) {
                    list($key, $value) = array($value[0], $value[1]);
                }

                if (strpos($key, ':') !== false) {
                    throw new InvalidKVFormat(sprintf(OpenIdErrorMessages::InvalidKVFormatChar, $key, ':'));
                }

                if (strpos($key, "\n") !== false) {
                    throw new InvalidKVFormat(sprintf(OpenIdErrorMessages::InvalidKVFormatChar, $key, '\\n'));
                }

                if (strpos($value, "\n") !== false) {
                    throw new InvalidKVFormat(sprintf(OpenIdErrorMessages::InvalidKVFormatChar, $value, '\\n'));
                }
                $kv_format .= "$key:$value\n";
            }
        }
        return $kv_format;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return self::OpenIdDirectResponse;
    }
}