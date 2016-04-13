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
use OpenId\Extensions\OpenIdExtension;
use OpenId\OpenIdProtocol;
use OpenId\Requests\Contexts\RequestContext;
use OpenId\Responses\Contexts\ResponseContext;
use OpenId\Requests\OpenIdRequest;
use OpenId\Responses\OpenIdResponse;
use Utils\Services\ILogService;
/**
 * Class OpenIdPAPEExtension
 * Implements http://openid.net/specs/openid-provider-authentication-policy-extension-1_0.html
 * @package OpenId\Extensions\Implementations
 */
class OpenIdPAPEExtension extends OpenIdExtension
{

    const Prefix = "pape";

    /**
     * OpenIdPAPEExtension constructor.
     * @param string $name
     * @param string $namespace
     * @param string $view
     * @param string $description
     * @param ILogService $log_service
     */
    public function __construct($name, $namespace, $view, $description, ILogService $log_service)
    {
        parent::__construct($name, $namespace, $view, $description,$log_service);
    }

    /**
     * @param $param
     * @param string $separator
     * @return string
     */
    public static function param($param, $separator = '.')
    {
        return OpenIdProtocol::OpenIdPrefix . $separator . self::Prefix . $separator . $param;
    }

    /**
     * @param string $separator
     * @return string
     */
    public static function paramNamespace($separator = '.')
    {
        return OpenIdProtocol::OpenIdPrefix . $separator . OpenIdProtocol::OpenIDProtocol_NS . $separator . self::Prefix;
    }

    public function parseRequest(OpenIdRequest $request, RequestContext $context)
    {
        // TODO: Implement parseRequest() method.
    }

    public function prepareResponse(OpenIdRequest $request, OpenIdResponse $response, ResponseContext $context)
    {
        // TODO: Implement prepareResponse() method.
    }

    public function getTrustedData(OpenIdRequest $request)
    {

    }

    protected function populateProperties()
    {
        // TODO: Implement populateProperties() method.
    }
}