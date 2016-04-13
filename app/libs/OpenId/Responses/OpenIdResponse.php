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
use Utils\Http\HttpResponse;
use OpenId\Exceptions\InvalidOpenIdMessageMode;
use OpenId\Helpers\OpenIdErrorMessages;
/**
 * Class OpenIdResponse
 * @package OpenId\Responses
 */
abstract class OpenIdResponse extends HttpResponse
{

    /**
     * OpenIdResponse constructor.
     * @param int $http_code
     * @param string $content_type
     */
    public function __construct($http_code, $content_type)
    {
        parent::__construct($http_code, $content_type);
    }

    /**
     * @param string $mode
     * @throws InvalidOpenIdMessageMode
     */
    protected function setMode($mode)
    {
        if (!OpenIdProtocol::isValidMode($mode))
            throw new InvalidOpenIdMessageMode(sprintf(OpenIdErrorMessages::InvalidOpenIdMessageModeMessage, $mode));
        $this->container[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)] = $mode;;
    }

}