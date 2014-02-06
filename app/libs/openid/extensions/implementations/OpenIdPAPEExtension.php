<?php
namespace openid\extensions\implementations;

use openid\extensions\OpenIdExtension;
use openid\OpenIdProtocol;
use openid\requests\contexts\RequestContext;
use openid\requests\OpenIdRequest;
use openid\responses\contexts\ResponseContext;
use openid\responses\OpenIdResponse;
use utils\services\ILogService;
/**
 * Class OpenIdPAPEExtension
 * Implements http://openid.net/specs/openid-provider-authentication-policy-extension-1_0.html
 * @package openid\extensions\implementations
 */
class OpenIdPAPEExtension extends OpenIdExtension
{

    const Prefix = "pape";

    public function __construct($name, $namespace, $view, $description, ILogService $log_service)
    {
        parent::__construct($name, $namespace, $view, $description,$log_service);
    }

    public static function param($param, $separator = '.')
    {
        return OpenIdProtocol::OpenIdPrefix . $separator . self::Prefix . $separator . $param;
    }

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