<?php

namespace openid\extensions\implementations;

use openid\exceptions\InvalidOpenIdMessageException;
use openid\helpers\OpenIdErrorMessages;
use openid\OpenIdMessage;
use openid\requests\OpenIdRequest;


/**
 * Class OpenIdAXRequest
 * Implements http://openid.net/specs/openid-attribute-exchange-1_0.html
 * @package openid\extensions\implementations
 */
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
    public function isValid()
    {

        //check identifier
        if (isset($this->message[OpenIdAXExtension::paramNamespace('_')])
            && $this->message[OpenIdAXExtension::paramNamespace('_')] == OpenIdAXExtension::NamespaceUrl
        ) {

            //check required fields

            if (!isset($this->message[OpenIdAXExtension::param(OpenIdAXExtension::Mode, '_')])
                || $this->message[OpenIdAXExtension::param(OpenIdAXExtension::Mode, '_')] != OpenIdAXExtension::FetchRequest
            )
                throw new InvalidOpenIdMessageException(OpenIdErrorMessages::AXInvalidModeMessage);

            if (!isset($this->message[OpenIdAXExtension::param(OpenIdAXExtension::RequiredAttributes, '_')]))
                throw new InvalidOpenIdMessageException(OpenIdErrorMessages::AXInvalidRequiredAttributesMessage);

            //get attributes
            $attributes = $this->message[OpenIdAXExtension::param(OpenIdAXExtension::RequiredAttributes, '_')];
            $attributes = explode(",", $attributes);

            foreach ($attributes as $attr) {
                $attr = trim($attr);
                if (!isset(OpenIdAXExtension::$available_properties[$attr]))
                    continue;
                if (!isset($this->message[OpenIdAXExtension::param(OpenIdAXExtension::Type, '_') . "_" . $attr]))
                    throw new InvalidOpenIdMessageException(sprintf(OpenIdErrorMessages::AXInvalidNamespaceMessage, $attr));
                $ns = $this->message[OpenIdAXExtension::param(OpenIdAXExtension::Type, "_") . "_" . $attr];
                if ($ns != OpenIdAXExtension::$available_properties[$attr])
                    throw new InvalidOpenIdMessageException(sprintf(OpenIdErrorMessages::AXInvalidNamespaceMessage, $attr));
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