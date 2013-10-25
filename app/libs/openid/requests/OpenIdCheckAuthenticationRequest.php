<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/24/13
 * Time: 9:02 PM
 */

namespace openid\requests;


use openid\OpenIdProtocol;
use openid\OpenIdMessage;

class OpenIdCheckAuthenticationRequest extends OpenIdAuthenticationRequest {

    public function __construct(OpenIdMessage $message){
        parent::__construct($message);
    }

    public static function IsOpenIdCheckAuthenticationRequest(OpenIdMessage $message){
        $mode = $message->getMode();
        if($mode==OpenIdProtocol::CheckAuthenticationMode) return true;
        return false;
    }

    public function IsValid()
    {
        $mode = $this->getMode();
        $claimed_assoc = $this->getAssocHandle();
        if($mode== OpenIdProtocol::CheckAuthenticationMode
          && !is_null($claimed_assoc) && !empty($claimed_assoc)){
            return true;
        }
        return false;
    }

    public function getSig(){
        return $this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig,"_")];
    }

    public function getSigned(){
        return $this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed,"_")];
    }

    public function getNonce(){
        return  $this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Nonce,"_")];
    }

    public function getOPEndpoint(){
        return $this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint,"_")];
    }

    public function getInvalidateHandle(){
        return $this->message[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_InvalidateHandle,"_")];
    }
}