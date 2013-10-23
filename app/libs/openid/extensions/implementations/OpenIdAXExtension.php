<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/16/13
 * Time: 2:41 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\extensions\implementations;


use openid\extensions\OpenIdExtension;
use openid\OpenIdProtocol;
use openid\requests\contexts\RequestContext;
use openid\requests\OpenIdRequest;
use openid\responses\contexts\ResponseContext;
use openid\responses\OpenIdResponse;
use openid\services\Registry;
use openid\OpenIdMessage;
use openid\exceptions\InvalidOpenIdMessageException;

class OpenIdAXRequest extends OpenIdRequest
{

    private $attributes;

    public function __construct(OpenIdMessage $message)
    {
        parent::__construct($message);
        $this->attributes = array();
    }

    /**
     * @return bool
     * @throws InvalidOpenIdMessageException
     */
    public function IsValid()
    {

        //check identifier
        if (
            isset($this->message[OpenIdProtocol::OpenIdPrefix . "_" . OpenIdProtocol::OpenIDProtocol_NS . "_" . OpenIdAXExtension::Prefix])
            && $this->message[OpenIdProtocol::OpenIdPrefix . "_" . OpenIdProtocol::OpenIDProtocol_NS . "_" . OpenIdAXExtension::Prefix] == OpenIdAXExtension::NamespaceUrl
        ) {

            //check required fields

            if (!isset($this->message[OpenIdProtocol::OpenIdPrefix . "_" . OpenIdAXExtension::Prefix . "_" . OpenIdAXExtension::Mode])
                || $this->message[OpenIdProtocol::OpenIdPrefix . "_" . OpenIdAXExtension::Prefix . "_" . OpenIdAXExtension::Mode] != OpenIdAXExtension::FetchRequest
            )
                throw new InvalidOpenIdMessageException("AX: not set or invalid mode mode");

            if (!isset($this->message[OpenIdProtocol::OpenIdPrefix . "_" . OpenIdAXExtension::Prefix . "_" . OpenIdAXExtension::RequiredAttributes]))
                throw new InvalidOpenIdMessageException("AX: not set required attributes!");

            $attributes = $this->message[OpenIdProtocol::OpenIdPrefix . "_" . OpenIdAXExtension::Prefix . "_" . OpenIdAXExtension::RequiredAttributes];
            $attributes = explode(",", $attributes);
            foreach ($attributes as $attr) {
                $attr = trim($attr);
                if (!isset(OpenIdAXExtension::$available_properties[$attr]))
                    //throw new InvalidOpenIdMessageException(sprintf("AX: invalid attribute requested %s", $attr));
                    continue;
                if (!isset($this->message[OpenIdProtocol::OpenIdPrefix . "_" . OpenIdAXExtension::Prefix . "_" . OpenIdAXExtension::Type . "_" . $attr]))
                    throw new InvalidOpenIdMessageException(sprintf("AX: invalid ns for attribute %s", $attr));
                $ns = $this->message[OpenIdProtocol::OpenIdPrefix . "_" . OpenIdAXExtension::Prefix . "_" . OpenIdAXExtension::Type . "_" . $attr];
                if ($ns != OpenIdAXExtension::$available_properties[$attr])
                    throw new InvalidOpenIdMessageException(sprintf("AX: invalid ns for attribute %s", $attr));
                array_push($this->attributes, $attr);
            }
            return true;
        }
        return false;

    }

    public function getRequiredAttributes()
    {
        return $this->attributes;
    }
}

class OpenIdAXExtension extends OpenIdExtension
{
    public static $available_properties;
    const Prefix = "ax";
    const NamespaceUrl = "http://openid.net/srv/ax/1.0";
    const RequiredAttributes = "required";
    const Mode = "mode";
    const Country = "country";
    const Email = "email";
    const FirstMame = "firstname";
    const Language = "language";
    const LastName = "lastname";
    const Type = "type";
    const Value = "value";
    const FetchResponse = "fetch_response";
    const FetchRequest = "fetch_request";

    public function __construct($name, $namespace, $description)
    {
        parent::__construct($name, $namespace, $description);
        self::$available_properties[OpenIdAXExtension::Country] = "http://axschema.org/contact/country/home";
        self::$available_properties[OpenIdAXExtension::Email] = "http://axschema.org/contact/email";
        self::$available_properties[OpenIdAXExtension::FirstMame] = "http://axschema.org/namePerson/first";
        self::$available_properties[OpenIdAXExtension::LastName] = "http://axschema.org/namePerson/last";
        self::$available_properties[OpenIdAXExtension::Language] = "http://axschema.org/pref/language";
    }


    public function parseRequest(OpenIdRequest $request, RequestContext $context)
    {
        $ax_request = new OpenIdAXRequest($request->getMessage());
        if (!$ax_request->IsValid()) return;
        //todo : build sub view ....
    }

    public function prepareResponse(OpenIdRequest $request, OpenIdResponse $response, ResponseContext $context)
    {
        $ax_request = new OpenIdAXRequest($request->getMessage());
        if (!$ax_request->IsValid()) return;
        $response->addParam(OpenIdProtocol::OpenIdPrefix . "." . OpenIdProtocol::OpenIDProtocol_NS . "." . self::Prefix, self::NamespaceUrl);
        $response->addParam(OpenIdProtocol::OpenIdPrefix . "." . self::Prefix . "." . self::Mode, self::FetchResponse);
        $context->addSignParam(OpenIdProtocol::OpenIdPrefix . "." . self::Prefix . "." . self::Mode);
        $attributes = $ax_request->getRequiredAttributes();
        $auth_service = Registry::getInstance()->get("openid\\services\\IAuthService");
        $user = $auth_service->getCurrentUser();
        foreach ($attributes as $attr) {
            $response->addParam(OpenIdProtocol::OpenIdPrefix . "." . self::Prefix . "." . self::Type . "." . $attr, self::$available_properties[$attr]);
            $context->addSignParam(OpenIdProtocol::OpenIdPrefix . "." . self::Prefix . "." . self::Type . "." . $attr);
            $context->addSignParam(OpenIdProtocol::OpenIdPrefix . "." . self::Prefix . "." . self::Value . "." . $attr);
            if ($attr == "email") {
                $response->addParam(OpenIdProtocol::OpenIdPrefix . "." . self::Prefix . "." . self::Value . "." . $attr, $user->getEmail());
            }
            if ($attr == "country") {
                $response->addParam(OpenIdProtocol::OpenIdPrefix . "." . self::Prefix . "." . self::Value . "." . $attr, $user->getCountry());
            }
            if ($attr == "firstname") {
                $response->addParam(OpenIdProtocol::OpenIdPrefix . "." . self::Prefix . "." . self::Value . "." . $attr, $user->getFirstName());
            }
            if ($attr == "lastname") {
                $response->addParam(OpenIdProtocol::OpenIdPrefix . "." . self::Prefix . "." . self::Value . "." . $attr, $user->getLastName());
            }
            if ($attr == "language") {
                $response->addParam(OpenIdProtocol::OpenIdPrefix . "." . self::Prefix . "." . self::Value . "." . $attr, $user->getLanguage());
            }
        }
    }

    public function getTrustedData(OpenIdRequest $request){

        $data = array();
        $ax_request = new OpenIdAXRequest($request->getMessage());
        if ($ax_request->IsValid()){
            $attributes = $ax_request->getRequiredAttributes();
            foreach($attributes as $attr){
                array_push($data,$attr);
            }
        }
        return $data;
    }


}