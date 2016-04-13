<?php namespace OAuth2\Responses;
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
use Utils\Http\HttpContentType;
/**
 * Class OAuth2IndirectResponse
 * @package OAuth2\Responses
 */
abstract class OAuth2IndirectResponse extends OAuth2Response
{

    /**
     * @var string
     */
    protected $return_to;

    const OAuth2IndirectResponse      = "OAuth2IndirectResponse";

    public function __construct()
    {
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct(self::HttpOkResponse, HttpContentType::Form);
    }

    public function getType()
    {
        return self::OAuth2IndirectResponse;
    }

    public function setReturnTo($return_to)
    {
        $this->return_to = $return_to;
    }

    public function getReturnTo()
    {
        return $this->return_to;
    }

    public function getContent()
    {
        $url_encoded_format = "";
        if ($this->container !== null)
        {
            ksort($this->container);
            foreach ($this->container as $key => $value)
            {
                if (is_array($value))
                {
                    list($key, $value) = array($value[0], $value[1]);
                }
                $value = urlencode($value);
                $url_encoded_format .= "$key=$value&";
            }
            $url_encoded_format = rtrim($url_encoded_format, '&');
        }
        return $url_encoded_format;
    }

    public function getContentType()
    {
        return HttpContentType::Form;
    }
} 