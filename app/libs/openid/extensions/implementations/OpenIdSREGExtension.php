<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/16/13
 * Time: 2:42 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\extensions\implementations;

use openid\extensions\OpenIdExtension;
use openid\requests\contexts\RequestContext;
use openid\requests\OpenIdRequest;
use openid\responses\contexts\ResponseContext;
use openid\responses\OpenIdResponse;
use openid\services\Registry;
use openid\services\ServiceCatalog;
use openid\OpenIdProtocol;
use openid\requests\contexts\PartialView;

class OpenIdSREGExtension extends OpenIdExtension
{

    const Prefix         = "sreg";
    const NamespaceUrl   = "http://openid.net/extensions/sreg/1.1";
    const NamespaceType  = 'ns';
    const Required       = 'required';
    const Optional       = 'optional';
    const PolicyUrl      = 'policy_url';

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


    public static $available_properties;


    public function __construct($name, $namespace,$view, $description)
    {
        parent::__construct($name, $namespace,$view, $description);
        self::$available_properties[OpenIdSREGExtension::Nickname]       = OpenIdSREGExtension::Nickname;
        self::$available_properties[OpenIdSREGExtension::Email]          = OpenIdSREGExtension::Email;
        self::$available_properties[OpenIdSREGExtension::FullName]       = OpenIdSREGExtension::FullName;
        self::$available_properties[OpenIdSREGExtension::Country]        = OpenIdSREGExtension::Country;
        self::$available_properties[OpenIdSREGExtension::Language]       = OpenIdSREGExtension::Language;
        self::$available_properties[OpenIdSREGExtension::Gender]         = OpenIdSREGExtension::Gender;
        self::$available_properties[OpenIdSREGExtension::DateOfBirthday] = OpenIdSREGExtension::DateOfBirthday;
        self::$available_properties[OpenIdSREGExtension::Postcode]       = OpenIdSREGExtension::Postcode;
        self::$available_properties[OpenIdSREGExtension::Timezone]       = OpenIdSREGExtension::Timezone;
    }

    public function parseRequest(OpenIdRequest $request, RequestContext $context)
    {
        $simple_reg_request = new OpenIdSREGRequest($request->getMessage());

        if (!$simple_reg_request->IsValid()) return;
        $attributes     = $simple_reg_request->getRequiredAttributes();
        $opt_attributes = $simple_reg_request->getOptionalAttributes();
        $policy_url     = $simple_reg_request->getPolicyUrl();
        $attributes     = array_merge($attributes,$opt_attributes);

        $view_data = array('attributes' => array_keys($attributes));

        if(!empty($policy_url)){
            $view_data['policy_url'] = $policy_url;
        }

        $partial_view = new PartialView($this->view, $view_data);
        $context->addPartialView($partial_view);
    }

    public function prepareResponse(OpenIdRequest $request, OpenIdResponse $response, ResponseContext $context)
    {
        $simple_reg_request = new OpenIdSREGRequest($request->getMessage());
        if (!$simple_reg_request->IsValid()) return;

        $response->addParam(self::paramNamespace(), self::NamespaceUrl);
        $attributes       = $simple_reg_request->getRequiredAttributes();
        $opt_attributes   = $simple_reg_request->getOptionalAttributes();
        $attributes       = array_merge($attributes,$opt_attributes);

        $auth_service = Registry::getInstance()->get(ServiceCatalog::AuthenticationService);
        $user = $auth_service->getCurrentUser();

        foreach ($attributes as $attr=>$value) {
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
    }

    public function getTrustedData(OpenIdRequest $request){
        $data = array();
        $simple_reg_request = new OpenIdSREGRequest($request->getMessage());
        if ($simple_reg_request->IsValid()){
            $attributes     = $simple_reg_request->getRequiredAttributes();
            $opt_attributes = $simple_reg_request->getOptionalAttributes();
            $attributes     = array_merge($attributes,$opt_attributes);
            foreach($attributes as $key=>$value){
                array_push($data,$key);
            }
        }
        return $data;
    }

    public static function param($param, $separator='.'){
        return OpenIdProtocol::OpenIdPrefix.$separator.self::Prefix.$separator.$param;
    }

    public static function paramNamespace($separator='.'){
        return OpenIdProtocol::OpenIdPrefix . $separator . OpenIdProtocol::OpenIDProtocol_NS . $separator . self::Prefix;
    }
}