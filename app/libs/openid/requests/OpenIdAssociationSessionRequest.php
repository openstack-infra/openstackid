<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/25/13
 * Time: 5:54 PM
 */

namespace openid\requests;

use openid\OpenIdProtocol;
use openid\OpenIdMessage;

class OpenIdAssociationSessionRequest extends OpenIdRequest{



    public function __construct(OpenIdMessage $message){
        parent::__construct($message);
    }


    public function IsValid()
    {
        return true;
    }

    public function getAssocType(){
        return $this->getParam(OpenIdProtocol::OpenIDProtocol_AssocType);
    }

    public function getSessionType(){
        return $this->getParam(OpenIdProtocol::OpenIDProtocol_SessionType);
    }

    public static function IsOpenIdAssociationSessionRequest(OpenIdMessage $message){
        $mode = $message->getMode();
        if($mode==OpenIdProtocol::AssociateMode) return true;
        return false;
    }
}