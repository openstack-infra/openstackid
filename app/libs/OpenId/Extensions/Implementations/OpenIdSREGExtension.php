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
use OpenId\Requests\Contexts\PartialView;
use OpenId\Requests\Contexts\RequestContext;
use OpenId\Responses\Contexts\ResponseContext;
use OpenId\Requests\OpenIdRequest;
use OpenId\Responses\OpenIdResponse;
use Utils\Services\IAuthService;
use Utils\Services\ILogService;
use Exception;
/**
 * Class OpenIdSREGExtension
 * Implements @see http://openid.net/specs/openid-simple-registration-extension-1_0.html
 * @package OpenId\Extensions\Implementations
 */
class OpenIdSREGExtension extends OpenIdExtension
{

    const Prefix        = 'sreg';
    const NamespaceUrl  = 'http://openid.net/extensions/sreg/1.1';
    const NamespaceType = 'ns';
    const Required      = 'required';
    const Optional      = 'optional';
    const PolicyUrl     = 'policy_url';

    //properties
    const Nickname       = 'nickname';
    const Email          = 'email';
    const FullName       = 'fullname';
    const DateOfBirthday = 'dob';
    const Gender         = 'gender';
    const Postcode       = 'postcode';
    const Country        = 'country';
    const Language       = 'language';
    const Timezone       = 'timezone';

    /**
     * @var array
     */
    public static $available_properties = array();

    /**
     * @var IAuthService
     */
	private $auth_service;

	/**
	 * @param              $name
	 * @param              $namespace
	 * @param              $view_name
	 * @param              $description
	 * @param IAuthService $auth_service
	 * @param ILogService  $log_service
	 */
	public function __construct($name, $namespace, $view_name , $description,
                                IAuthService $auth_service,
                                ILogService $log_service)
    {
        parent::__construct($name, $namespace, $view_name, $description,$log_service);

	    $this->auth_service = $auth_service;

        self::$available_properties[OpenIdSREGExtension::Nickname] = OpenIdSREGExtension::Nickname;
        self::$available_properties[OpenIdSREGExtension::Email] = OpenIdSREGExtension::Email;
        self::$available_properties[OpenIdSREGExtension::FullName] = OpenIdSREGExtension::FullName;
        self::$available_properties[OpenIdSREGExtension::Country] = OpenIdSREGExtension::Country;
        self::$available_properties[OpenIdSREGExtension::Language] = OpenIdSREGExtension::Language;
        self::$available_properties[OpenIdSREGExtension::Gender] = OpenIdSREGExtension::Gender;
        self::$available_properties[OpenIdSREGExtension::DateOfBirthday] = OpenIdSREGExtension::DateOfBirthday;
        self::$available_properties[OpenIdSREGExtension::Postcode] = OpenIdSREGExtension::Postcode;
        self::$available_properties[OpenIdSREGExtension::Timezone] = OpenIdSREGExtension::Timezone;
    }

    public function parseRequest(OpenIdRequest $request, RequestContext $context)
    {
        try {

            $simple_reg_request = new OpenIdSREGRequest($request->getMessage());

            if (!$simple_reg_request->isValid()) return;
            $attributes = $simple_reg_request->getRequiredAttributes();
            $opt_attributes = $simple_reg_request->getOptionalAttributes();
            $policy_url = $simple_reg_request->getPolicyUrl();
            $attributes = array_merge($attributes, $opt_attributes);

            $view_data = array('attributes' => array_keys($attributes));

            if (!empty($policy_url)) {
                $view_data['policy_url'] = $policy_url;
            }

            $partial_view = new PartialView($this->view, $view_data);
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
            $simple_reg_request = new OpenIdSREGRequest($request->getMessage());
            if (!$simple_reg_request->isValid()) return;

            $response->addParam(self::paramNamespace(), self::NamespaceUrl);
            $attributes = $simple_reg_request->getRequiredAttributes();
            $opt_attributes = $simple_reg_request->getOptionalAttributes();
            $attributes = array_merge($attributes, $opt_attributes);

            $user = $this->auth_service->getCurrentUser();

            foreach ($attributes as $attr => $value) {
                $context->addSignParam(self::param($attr));

                if ($attr == self::Email) {
                    $response->addParam(self::param($attr), $user->getEmail());
                }
                if ($attr == self::Country) {
                    $response->addParam(self::param($attr), $user->getCountry());
                }
                if ($attr == self::Nickname || $attr == self::FullName) {
                    $response->addParam(self::param($attr), $user->getFullName());
                }
                if ($attr == self::Language) {
                    $response->addParam(self::param($attr), $user->getLanguage());
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
            $simple_reg_request = new OpenIdSREGRequest($request->getMessage());

            if ($simple_reg_request->isValid()) {

                $attributes     = $simple_reg_request->getRequiredAttributes();
                $opt_attributes = $simple_reg_request->getOptionalAttributes();
                $attributes     = array_merge($attributes, $opt_attributes);

                foreach ($attributes as $key => $value) {
                    array_push($data, $key);
                }
            }
        } catch (Exception $ex) {
            $this->log_service->debug_msg($request->__toString());
            $this->log_service->error($ex);
        }
        return $data;
    }
}