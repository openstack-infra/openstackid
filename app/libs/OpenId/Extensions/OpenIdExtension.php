<?php namespace OpenId\Extensions;
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
use OpenId\Requests\contexts\RequestContext;
use OpenId\Requests\OpenIdRequest;
use OpenId\Responses\Contexts\ResponseContext;
use OpenId\Responses\OpenIdResponse;
use Utils\Services\ILogService;
use OpenId\Exceptions\InvalidOpenIdMessageException;
/**
 * Class OpenIdExtension
 * Abstract implementation of OpenId Extensions
 * @see http://openid.net/specs/openid-authentication-2_0.html#extensions
 * @package OpenId\Extensions
 */
abstract class OpenIdExtension
{
    /**
     * @var string
     */
    protected $namespace;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var string
     */
    protected $view;

    /**
     * @var ILogService
     */
    protected $log_service;

	/**
	 * @param string      $name
	 * @param string      $namespace
	 * @param string      $view_name
	 * @param string      $description
	 * @param ILogService $log_service
	 */
	public function __construct($name, $namespace, $view_name, $description, ILogService $log_service)
    {
        $this->namespace   = $namespace;
        $this->name        = $name;
        $this->view        = $view_name;
        $this->description = $description;
        $this->log_service = $log_service;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /** parse extension request
     * @param OpenIdRequest $request
     * @param RequestContext $context
     * @return mixed
     * @throws InvalidOpenIdMessageException
     */
    abstract public function parseRequest(OpenIdRequest $request, RequestContext $context);

    /** Get a set of data that user allowed to be marked as trusted for futures request
     * @param OpenIdRequest $request
     * @return mixed
     */
    abstract public function getTrustedData(OpenIdRequest $request);

    /** build extension response
     * @param OpenIdRequest $request
     * @param OpenIdResponse $response
     * @param ResponseContext $context
     * @return mixed
     */
    abstract public function prepareResponse(OpenIdRequest $request, OpenIdResponse $response, ResponseContext $context);
}