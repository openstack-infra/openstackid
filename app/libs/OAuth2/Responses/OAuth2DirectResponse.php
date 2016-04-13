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
 * Class OAuth2DirectResponse
 * @package OAuth2\Responses
 */
class OAuth2DirectResponse extends OAuth2Response
{

    const OAuth2DirectResponse  = 'OAuth2DirectResponse';

    public function __construct($http_code = self::HttpOkResponse, $content_type = HttpContentType::Json)
    {
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct($http_code,$content_type );
    }

    public function getContent()
    {
        $json_encoded_format = json_encode($this->container);
        return $json_encoded_format;
    }

    public function getType()
    {
        return self::OAuth2DirectResponse;
    }
}