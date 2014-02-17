<?php

namespace openid\extensions;

use openid\requests\contexts\RequestContext;
use openid\requests\OpenIdRequest;
use openid\responses\contexts\ResponseContext;
use openid\responses\OpenIdResponse;
use utils\services\ILogService;

/**
 * Class OpenIdExtension
 * Abstract implementation of OpenId Extensions
 * http://openid.net/specs/openid-authentication-2_0.html#extensions
 * @package openid\extensions
 */
abstract class OpenIdExtension
{

    protected $namespace;
    protected $name;
    protected $description;
    protected $view;

    protected $log_service;

	/**
	 * @param             $name
	 * @param             $namespace
	 * @param             $view_name
	 * @param             $description
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