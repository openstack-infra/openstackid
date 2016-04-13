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
use Exception;
use OpenId\Extensions\OpenIdExtension;
use OpenId\OpenIdProtocol;
use OpenId\Requests\Contexts\PartialView;
use OpenId\Requests\Contexts\RequestContext;
use OpenId\Requests\OpenIdRequest;
use OpenId\Responses\Contexts\ResponseContext;
use OpenId\Responses\OpenIdResponse;
use Utils\Services\ILogService;
use Utils\Services\IAuthService;
/**
 * Class OpenIdAXExtension
 * Implements
 * http://openid.net/specs/openid-attribute-exchange-1_0.html
 * @package OpenId\Extensions\Implementations
 */
class OpenIdAXExtension extends OpenIdExtension
{
    const Prefix             = "ax";
    const NamespaceUrl       = "http://openid.net/srv/ax/1.0";
    const RequiredAttributes = "required";
    const Mode               = "mode";
    const Country            = "country";
    const Email              = "email";
    const FirstMame          = "firstname";
    const Language           = "language";
    const LastName           = "lastname";
    const Type               = "type";
    const Value              = "value";
    const FetchResponse      = "fetch_response";
    const FetchRequest       = "fetch_request";
    /**
     * @var array
     */
    public static $available_properties = array();

    /**
     * @var IAuthService
     */
	private $auth_service;

	/**
	 * @param string       $name
	 * @param string       $namespace
	 * @param string       $view_name
	 * @param string       $description
	 * @param IAuthService $auth_service
	 * @param ILogService  $log_service
	 */
	public function __construct($name, $namespace, $view_name, $description,
                                IAuthService $auth_service,
                                ILogService $log_service)
    {
        parent::__construct($name, $namespace, $view_name, $description, $log_service);

	    $this->auth_service = $auth_service;

        self::$available_properties[OpenIdAXExtension::Country]   = "http://axschema.org/contact/country/home";
        self::$available_properties[OpenIdAXExtension::Email]     = "http://axschema.org/contact/email";
        self::$available_properties[OpenIdAXExtension::FirstMame] = "http://axschema.org/namePerson/first";
        self::$available_properties[OpenIdAXExtension::LastName]  = "http://axschema.org/namePerson/last";
        self::$available_properties[OpenIdAXExtension::Language]  = "http://axschema.org/pref/language";
    }

    /**
     * @param OpenIdRequest $request
     * @param RequestContext $context
     * @return void
     */
    public function parseRequest(OpenIdRequest $request, RequestContext $context)
    {
        try {
            $ax_request = new OpenIdAXRequest($request->getMessage());
            if (!$ax_request->isValid()) return;
            $attributes = $ax_request->getRequiredAttributes();
            $data = array();
            foreach ($attributes as $attr) {
                array_push($data, $attr);
            }
            $partial_view = new PartialView($this->view, array("attributes" => $data));
            $context->addPartialView($partial_view);
        } catch (Exception $ex) {
            $this->log_service->error($ex);
        }
    }

    /**
     * @param OpenIdRequest $request
     * @param OpenIdResponse $response
     * @param ResponseContext $context
     * @return void
     */
    public function prepareResponse(OpenIdRequest $request, OpenIdResponse $response, ResponseContext $context)
    {
        try {
            $ax_request = new OpenIdAXRequest($request->getMessage());
            if (!$ax_request->isValid()) return;

            $response->addParam(self::paramNamespace(), self::NamespaceUrl);
            $response->addParam(self::param(self::Mode), self::FetchResponse);
            $context->addSignParam(self::param(self::Mode));

            $attributes   = $ax_request->getRequiredAttributes();
            $user         = $this->auth_service->getCurrentUser();

            foreach ($attributes as $attr) {
                $response->addParam(self::param(self::Type) . "." . $attr, self::$available_properties[$attr]);
                $context->addSignParam(self::param(self::Type) . "." . $attr);
                $context->addSignParam(self::param(self::Value) . "." . $attr);
                if ($attr == "email") {
                    $response->addParam(self::param(self::Value) . "." . $attr, $user->getEmail());
                }
                if ($attr == "country") {
                    $response->addParam(self::param(self::Value) . "." . $attr, $user->getCountry());
                }
                if ($attr == "firstname") {
                    $response->addParam(self::param(self::Value) . "." . $attr, $user->getFirstName());
                }
                if ($attr == "lastname") {
                    $response->addParam(self::param(self::Value) . "." . $attr, $user->getLastName());
                }
                if ($attr == "language") {
                    $response->addParam(self::param(self::Value) . "." . $attr, $user->getLanguage());
                }
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
        }
    }

    /**
     * @param string $separator
     * @return string
     */
    public static function paramNamespace($separator = '.')
    {
        return OpenIdProtocol::OpenIdPrefix . $separator . OpenIdProtocol::OpenIDProtocol_NS . $separator . self::Prefix;
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
     * @param OpenIdRequest $request
     * @return array
     */
    public function getTrustedData(OpenIdRequest $request)
    {
        $data = array();
        try {
            $ax_request = new OpenIdAXRequest($request->getMessage());
            if ($ax_request->isValid()) {
                $attributes = $ax_request->getRequiredAttributes();
                foreach ($attributes as $attr) {
                    array_push($data, $attr);
                }
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
        }
        return $data;
    }
}