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

class OpenIdAuthenticationRequest extends OpenIdRequest{
    const IdentifierSelectType = "http://specs.openid.net/auth/2.0/identifier_select";
    const ImmediateMode        = "checkid_immediate";
    const SetupMode            = "checkid_setup";

    const ClaimedIdType   = "openid_claimed_id";
    const IdentityType    = "openid_identity";
    const AssocHandleType = "openid_assoc_handle";
    const ReturnToType    = "openid_return_to";
    const RealmType       = "openid_realm";

    public static function IsOpenIdAuthenticationRequest(OpenIdMessage $message){
        $mode = $message->getMode();
        if($mode==self::ImmediateMode || $mode==self::SetupMode) return true;
        return false;
    }

    public function getClaimedId(){
        return isset($this->message[self::ClaimedIdType])?$this->message[self::ClaimedIdType]:null;
    }

    public function getIdentity(){
        return isset($this->message[self::IdentityType])?$this->message[self::IdentityType]:null;
    }

    public function getAssocHandle(){
        return isset($this->message[self::AssocHandleType])?$this->message[self::AssocHandleType]:null;
    }

    public function getReturnTo(){
        return isset($this->message[self::ReturnToType])?$this->message[self::ReturnToType]:null;
    }

    public function getRealm(){
        return isset($this->message[self::RealmType])?$this->message[self::RealmType]:null;
    }

    public function IsValid(){
        $return_to = $this->getReturnTo();
        $claimed_id = $this->getClaimedId();
        $identity = $this->getIdentity();
        $mode = $this->getMode();
        //todo: validate url(format-regex) - white list /black list?
        return !empty($return_to)
               && !empty($claimed_id) && $claimed_id==self::IdentifierSelectType
               && !empty($identity) && $identity==self::IdentifierSelectType
               && !empty($mode) && ($mode == self::ImmediateMode || $mode == self::SetupMode);
    }

}