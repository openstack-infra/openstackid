<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 11/4/13
 * Time: 11:06 AM
 */

namespace openid\extensions\implementations;

use Exception;
use openid\OpenIdMessage;
use openid\requests\OpenIdRequest;
use openid\services\Registry;
use openid\services\ServiceCatalog;

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
    private $log;

    public function __construct(OpenIdMessage $message)
    {
        parent::__construct($message);
        $this->attributes = array();
        $this->optional_attributes = array();
        $this->log = Registry::getInstance()->get(ServiceCatalog::LogService);
    }

    public function isValid()
    {
        try {
            //check identifier
            if (isset($this->message[OpenIdSREGExtension::paramNamespace('_')])
                && $this->message[OpenIdSREGExtension::paramNamespace('_')] == OpenIdSREGExtension::NamespaceUrl
            ) {

                //check required fields

                if (!isset($this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Required, '_')]))
                    throw new InvalidOpenIdMessageException("SREG: not set required attributes!");

                //get attributes
                $attributes = $this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Required, '_')];
                $attributes = explode(",", $attributes);
                if (count($attributes) <= 0) {
                    throw new InvalidOpenIdMessageException("SREG: not set required attributes!");
                }

                foreach ($attributes as $attr) {
                    $attr = trim($attr);
                    if (!isset(OpenIdSREGExtension::$available_properties[$attr]))
                        continue;
                    $this->attributes[$attr] = $attr;
                }

                //get attributes
                if (isset($this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Optional, '_')])) {
                    $opt_attributes = $this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Optional, '_')];
                    $opt_attributes = explode(",", $opt_attributes);
                    foreach ($opt_attributes as $opt_attr) {
                        $opt_attr = trim($opt_attr);
                        if (!isset(OpenIdSREGExtension::$available_properties[$opt_attr]))
                            continue;
                        if (isset($this->attributes[$opt_attr]))
                            throw new InvalidOpenIdMessageException("SREG: optional attribute is already set as required one!");
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
            $this->log->error($ex);
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