<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 12:18 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\requests;

use openid\requests\OpenIdRequest;
use openid\OpenIdMessage;
use openid\OpenIdProtocol;
use openid\helpers\OpenIdUriHelper;

class OpenIdAuthenticationRequest extends OpenIdRequest {

    public function __construct(OpenIdMessage $message){
        parent::__construct($message);
    }

    public static function IsOpenIdAuthenticationRequest(OpenIdMessage $message){
        $mode = $message->getMode();
        if($mode==OpenIdProtocol::ImmediateMode || $mode==OpenIdProtocol::SetupMode) return true;
        return false;
    }

    public function getClaimedId(){
        return isset($this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId,"_")])?$this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId,"_")]:null;
    }

    public function getIdentity(){
        return isset($this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity,"_")])?$this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity,"_")]:null;
    }

    public function getAssocHandle(){
        return isset($this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocHandle,"_")])?$this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocHandle,"_")]:null;
    }

    public function getReturnTo(){
        $return_to = isset($this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo,"_")])?$this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo,"_")]:null;
        return (OpenIdUriHelper::checkReturnTo($return_to))?$return_to:"";
    }

    public function getRealm(){
        return isset($this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm,"_")])?$this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm,"_")]:null;
    }


    public function getTrustedRoot()    {
        if (isset($this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm,"_")])) {
            $root = $this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm,"_")];
        } else if (isset($this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo,"_")])) {
            $root = $this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo,"_")];
        } else {
            return null;
        }
        if (OpenIdUriHelper::normalizeUrl($root) && !empty($root)) {
            return $root;
        }
        return null;
    }

    /**
     * @param $claimed_id
     * @param $identity
     * @return bool
     */
    private function isValidIdentifier($claimed_id,$identity){
        if($claimed_id==$identity && $identity==OpenIdProtocol::IdentifierSelectType && $claimed_id==OpenIdProtocol::IdentifierSelectType)
            return true;
        if($claimed_id==$identity){
            //todo: check valid user?
            return true;
        }
        return false;
    }

    public function IsValid(){
        $return_to  = $this->getReturnTo();
        $claimed_id = $this->getClaimedId();
        $identity   = $this->getIdentity();
        $mode       = $this->getMode();
        $realm      = $this->getRealm();
        return !empty($return_to)
               && !empty($realm)
               && OpenIdUriHelper::checkRealm($realm,$return_to)
               && !empty($claimed_id)
               && !empty($identity)
               && $this->isValidIdentifier($claimed_id,$identity)
               && !empty($mode) && ($mode == OpenIdProtocol::ImmediateMode || $mode == OpenIdProtocol::SetupMode);
    }

}