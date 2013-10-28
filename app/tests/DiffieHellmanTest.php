<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/26/13
 * Time: 4:55 PM
 */
use openid\requests\OpenIdDHAssociationSessionRequest;
use openid\helpers\OpenIdCryptoHelper;
use \Zend\Crypt\PublicKey\DiffieHellman;
use \openid\helpers\AssocHandleGenerator;
class DiffieHellmanTest extends TestCase {

    /**
     *
     */
    public function testDefaultDHParams(){
        $g = OpenIdDHAssociationSessionRequest::DH_G;
        $p = OpenIdDHAssociationSessionRequest::DH_P;

        $g_bin = pack('H*',$g);
        $p_bin = pack('H*',$p);

        $g_number = OpenIdCryptoHelper::convert($g_bin,DiffieHellman::FORMAT_BINARY,DiffieHellman::FORMAT_NUMBER);
        $p_number = OpenIdCryptoHelper::convert($p_bin,DiffieHellman::FORMAT_BINARY,DiffieHellman::FORMAT_NUMBER);

        $this->assertTrue($g_number =='2');
        $this->assertTrue($p_number =='155172898181473697471232257763715539915724801966915404479707795314057629378541917580651227423698188993727816152646631438561595825688188889951272158842675419950341258706556549803580104870537681476726513255747040765857479291291572334510643245094715007229621094194349783925984760375594985848253359305585439638443');
    }

    public function testAssocHandlerGenerator(){
        $list = '';
        for($i=33;$i<=126;$i++){
            $list.= chr($i);
        }
        $handler = AssocHandleGenerator::generate(32);
        $this->assertTrue(strlen($handler)==32);
    }


    public function testAssociation(){
        $params = array(
            "openid.ns"=>"http://specs.openid.net/auth/2.0",
            "openid.assoc_type"=>'HMAC-SHA1',
            "openid.dh_consumer_public"=>'AKxUIFDn9RsntYCLmRwc2jx+V7jJEB+EQK7Kgcyck7c3yEJzyrBrB/8DUA4Dsllne2tQX+t+7ivneZMyFsVOyfskw9yZRKQU6poovNbqudW9kVrBtrggwEsjLChIwkKm13xXKVomtGH5pU2V8PwIZtPluMr8uRR+0R9ogixwVSkB',
            "openid.mode"=>'associate',
            "openid.session_type"=>"DH-SHA1",
        );

        $shared_secret = '63747905442891188011074543977271509700057281459936951909895661410344707875213381494657305044375401970833012559592307378035755377201544395671677989732074875047679599415079109546770345078156268045455589915055517469932823665407196575060345355634851325574327371573077018227834572392777937912458896556870551728909';
        $rp_rp_public_key ='121013270978594747679028556064764237584422563894035983088053529062131301635207919054508681115003074784588424674441119914004706414602938326107484749287157686396853941560348587266623270903309845335216158216107235323624947881403052366432191590393486081418713651288957096504910069887492752423639936941679055284481';


        $response = $this->client->request("POST","/accounts/openid/v2",$params);
    }


} 