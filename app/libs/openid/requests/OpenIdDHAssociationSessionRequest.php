<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/25/13
 * Time: 6:11 PM
 */

namespace openid\requests;

use openid\OpenIdProtocol;
use openid\OpenIdMessage;

class OpenIdDHAssociationSessionRequest extends OpenIdAssociationSessionRequest {


     // Default Diffie-Hellman key generator (1024 bit)
    const DH_P   = 'dcf93a0b883972ec0e19989ac5a2ce310e1d37717e8d9571bb7623731866e61ef75a2e27898b057f9891c2e27a639c3f29b60814581cd3b2ca3986d2683705577d45c2e7e52dc81c7a171876e5cea74b1448bfdfaf18828efd2519f14e45e3826634af1949e5b535cc829a483b8a76223e5d490a257f05bdff16f2fb22c583ab';

    // Default Diffie-Hellman prime number (should be 2 or 5)
    const DH_G   = '02';

    public function __construct(OpenIdMessage $message){
        parent::__construct($message);
    }

    public function IsValid()
    {
        $dh_modulus              = $this->getDHModulus();
        $dh_gen                  = $this->getDHGen();
        $dh_consumer_public      = $this->getDHConsumerPublic();
        if(!empty($dh_modulus) && !empty($dh_gen) && !empty($dh_consumer_public))
            return true;
        return true;
    }

    public function getDHModulus(){
        $p =  $this->getParam(OpenIdProtocol::OpenIdProtocol_DHModulus);
        return empty($p)?pack('H*', OpenIdDHAssociationSessionRequest::DH_P): base64_decode($p);
    }

    public function getDHGen(){
        $g =  $this->getParam(OpenIdProtocol::OpenIdProtocol_DHGen);
        return empty($g)?pack('H*', OpenIdDHAssociationSessionRequest::DH_G): base64_decode($g);
    }

    public function getDHConsumerPublic(){
        $pk = $this->getParam(OpenIdProtocol::OpenIdProtocol_DHConsumerPublic);
        return empty($pk)?"": base64_decode($pk);
    }

    public static function IsOpenIdDHAssociationSessionRequest(OpenIdMessage $message){
        if(OpenIdAssociationSessionRequest::IsOpenIdAssociationSessionRequest($message)){
            $session_type = $message->getParam(OpenIdProtocol::OpenIDProtocol_AssocType);
            if($session_type==OpenIdProtocol::AssociationSessionTypeDHSHA1 || $session_type==OpenIdProtocol::AssociationSessionTypeDHSHA256)
                return true;
        }
        return false;
    }

} 