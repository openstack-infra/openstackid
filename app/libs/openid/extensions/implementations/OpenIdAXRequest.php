<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 11/4/13
 * Time: 10:45 AM
 */

namespace openid\extensions\implementations;

use openid\requests\OpenIdRequest;
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
        if (isset($this->message[OpenIdAXExtension::paramNamespace('_')])
               && $this->message[OpenIdAXExtension::paramNamespace('_')] == OpenIdAXExtension::NamespaceUrl
        ) {

            //check required fields

            if (!isset($this->message[OpenIdAXExtension::param(OpenIdAXExtension::Mode,'_')])
                ||     $this->message[OpenIdAXExtension::param(OpenIdAXExtension::Mode,'_')] != OpenIdAXExtension::FetchRequest
            )
                throw new InvalidOpenIdMessageException("AX: not set or invalid mode mode");

            if (!isset($this->message[OpenIdAXExtension::param(OpenIdAXExtension::RequiredAttributes,'_')]))
                throw new InvalidOpenIdMessageException("AX: not set required attributes!");

            //get attributes
            $attributes = $this->message[OpenIdAXExtension::param(OpenIdAXExtension::RequiredAttributes,'_')];
            $attributes = explode(",", $attributes);

            foreach ($attributes as $attr) {
                $attr = trim($attr);
                if (!isset(OpenIdAXExtension::$available_properties[$attr]))
                    continue;
                if (!isset($this->message[OpenIdAXExtension::param(OpenIdAXExtension::Type,'_') . "_" . $attr]))
                    throw new InvalidOpenIdMessageException(sprintf("AX: invalid ns for attribute %s", $attr));
                $ns = $this->message[OpenIdAXExtension::param(OpenIdAXExtension::Type , "_") . "_" . $attr];
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