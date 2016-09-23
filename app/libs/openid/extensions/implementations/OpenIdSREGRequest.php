<?php

namespace openid\extensions\implementations;

use Exception;
use openid\OpenIdMessage;
use openid\requests\OpenIdRequest;
use openid\exceptions\InvalidOpenIdMessageException;

/**
 * Class OpenIdSREGRequest
 * Implements http://openid.net/specs/openid-simple-registration-extension-1_0.html
 * @package openid\extensions\implementations
 */
class OpenIdSREGRequest extends OpenIdRequest
{

    private $attributes;
    private $optional_attributes;
    private $policy_url;

    public function __construct(OpenIdMessage $message)
    {
        parent::__construct($message);
        $this->attributes = array();
        $this->optional_attributes = array();
    }

    public function isValid()
    {
        try {
            //check identifier
            if (isset($this->message[OpenIdSREGExtension::paramNamespace('_')])
                && $this->message[OpenIdSREGExtension::paramNamespace('_')] == OpenIdSREGExtension::NamespaceUrl
            ) {

                /*
                 * All of the following request fields are OPTIONAL, though at least one of "openid.sreg.required"
                 * or "openid.sreg.optional" MUST be specified in the request.
                 * openid.sreg.required:
                 * Comma-separated list of field names which, if absent from the response, will prevent the Consumer f
                 * rom completing the registration without End User interation. The field names are those that are
                 * specified in the Response Format, with the "openid.sreg." prefix removed.
                 * openid.sreg.optional:
                 * Comma-separated list of field names Fields that will be used by the Consumer, but whose absence will
                 * not prevent the registration from completing. The field names are those that are specified in the
                 * Response Format, with the "openid.sreg." prefix removed.
                 * openid.sreg.policy_url:
                 * A URL which the Consumer provides to give the End User a place to read about the how the profile data
                 * will be used. The Identity Provider SHOULD display this URL to the End User if it is given.
                 */

                //check required fields

                if( !isset($this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Required, '_')]) &&
                    !isset($this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Optional, '_')]))
                    throw new InvalidOpenIdMessageException("SREG: at least one of \"openid.sreg.required\" or \"openid.sreg.optional\" MUST be specified in the request.");

                //get attributes
                if (isset($this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Required, '_')])) {

                    $attributes = $this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Required, '_')];
                    $attributes = explode(",", $attributes);

                    foreach ($attributes as $attr) {
                        $attr = trim($attr);

                        if (!isset(OpenIdSREGExtension::$available_properties[$attr]))
                            continue;

                        $this->attributes[$attr] = $attr;
                    }
                }

                //get optional attributes
                if (isset($this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Optional, '_')])) {

                    $opt_attributes = $this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Optional, '_')];
                    $opt_attributes = explode(",", $opt_attributes);

                    foreach ($opt_attributes as $opt_attr) {
                        $opt_attr = trim($opt_attr);

                        if (!isset(OpenIdSREGExtension::$available_properties[$opt_attr]))
                            continue;

                        if (isset($this->attributes[$opt_attr]))
                            throw new InvalidOpenIdMessageException(sprintf("SREG: optional attribute %s is already set as required one!", $opt_attr));

                        $this->optional_attributes[$opt_attr] = $opt_attr;
                    }
                }

                //check policy url..
                if (isset($this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::PolicyUrl, '_')])) {
                    $this->policy_url = $this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::PolicyUrl, '_')];
                }

                return true;
            }
        } catch (Exception $ex) {
            throw $ex;
        }
        return false;
    }

    public function getRequiredAttributes()
    {
        return $this->attributes;
    }

    public function getOptionalAttributes()
    {
        return $this->optional_attributes;
    }

    public function getPolicyUrl()
    {
        return $this->policy_url;
    }
}