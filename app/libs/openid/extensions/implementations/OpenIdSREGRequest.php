<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 11/4/13
 * Time: 11:06 AM
 */

namespace openid\extensions\implementations;

use openid\extensions\OpenIdExtension;
use openid\OpenIdProtocol;
use openid\requests\contexts\RequestContext;
use openid\requests\OpenIdRequest;
use openid\responses\contexts\ResponseContext;
use openid\responses\OpenIdResponse;
use openid\services\Registry;
use openid\requests\contexts\PartialView;
use openid\services\ServiceCatalog;
use openid\OpenIdMessage;

class OpenIdSREGRequest  extends OpenIdRequest{

    private $attributes;
    private $optional_attributes;
    private $policy_url;

    public function __construct(OpenIdMessage $message)
    {
        parent::__construct($message);
        $this->attributes          = array();
        $this->optional_attributes = array();
    }

    public function IsValid()
    {
        //check identifier
        if (isset($this->message[OpenIdSREGExtension::paramNamespace('_')])
            && $this->message[OpenIdSREGExtension::paramNamespace('_')] == OpenIdSREGExtension::NamespaceUrl
        ) {

            //check required fields

            if (!isset($this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Required,'_')]))
                throw new InvalidOpenIdMessageException("SREG: not set required attributes!");

            //get attributes
            $attributes = $this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Required,'_')];
            $attributes = explode(",", $attributes);
            if(count($attributes)<=0){
                throw new InvalidOpenIdMessageException("SREG: not set required attributes!");
            }

            foreach ($attributes as $attr) {
                $attr = trim($attr);
                if (!isset(OpenIdSREGExtension::$available_properties[$attr]))
                    continue;
                $this->attributes[$attr]=$attr;
            }

            //get attributes
            if(isset($this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Optional,'_')])){
                $opt_attributes = $this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::Optional,'_')];
                $opt_attributes = explode(",", $opt_attributes);
                foreach ($opt_attributes as $opt_attr) {
                    $opt_attr = trim($opt_attr);
                    if (!isset(OpenIdSREGExtension::$available_properties[$opt_attr]))
                        continue;
                    if(isset($this->attributes[$opt_attr]))
                        throw new InvalidOpenIdMessageException("SREG: optional attribute is already set as required one!");
                    $this->optional_attributes[$opt_attr]=$opt_attr;
                }
            }

            //check policy url..
            if(isset($this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::PolicyUrl,'_')])){
                $this->policy_url = $this->message[OpenIdSREGExtension::param(OpenIdSREGExtension::PolicyUrl,'_')];
            }
            return true;
        }
        return false;
    }

    public function getRequiredAttributes()
    {
        return $this->attributes;
    }

    public function getOptionalAttributes(){
        return $this->optional_attributes;
    }

    public function getPolicyUrl(){
        return $this->policy_url;
    }
}