<?php

use openid\helpers\AssocHandleGenerator;
use openid\helpers\OpenIdCryptoHelper;
use openid\OpenIdProtocol;
use openid\requests\OpenIdDHAssociationSessionRequest;
use Zend\Crypt\PublicKey\DiffieHellman;

class DiffieHellmanTest extends TestCase
{

    /**
     *
     */
    public function testDefaultDHParams()
    {
        $g = OpenIdDHAssociationSessionRequest::DH_G;
        $p = OpenIdDHAssociationSessionRequest::DH_P;

        $g_bin = pack('H*', $g);
        $p_bin = pack('H*', $p);

        $g_number = OpenIdCryptoHelper::convert($g_bin, DiffieHellman::FORMAT_BINARY, DiffieHellman::FORMAT_NUMBER);
        $p_number = OpenIdCryptoHelper::convert($p_bin, DiffieHellman::FORMAT_BINARY, DiffieHellman::FORMAT_NUMBER);

        $this->assertTrue($g_number == '2');
        $this->assertTrue($p_number == '155172898181473697471232257763715539915724801966915404479707795314057629378541917580651227423698188993727816152646631438561595825688188889951272158842675419950341258706556549803580104870537681476726513255747040765857479291291572334510643245094715007229621094194349783925984760375594985848253359305585439638443');
    }

    public function testAssocHandlerGenerator()
    {
        $handler = AssocHandleGenerator::generate(32);
        $this->assertTrue(strlen($handler) == 32);
    }

    public function testAssociationMessage()
    {

        $g = pack('H*', OpenIdDHAssociationSessionRequest::DH_G);
        $g = OpenIdCryptoHelper::convert($g, DiffieHellman::FORMAT_BINARY, DiffieHellman::FORMAT_NUMBER);
        $p = pack('H*', OpenIdDHAssociationSessionRequest::DH_P);
        $p = OpenIdCryptoHelper::convert($p, DiffieHellman::FORMAT_BINARY, DiffieHellman::FORMAT_NUMBER);
        $dh = new DiffieHellman($p, $g);
        $dh->generateKeys();

        $rp_public_key = $dh->getPublicKey(DiffieHellman::FORMAT_BTWOC);
        $dh->computeSecretKey($rp_public_key, DiffieHellman::FORMAT_BTWOC);
        $rp_public_key = base64_encode($rp_public_key);
        $shared_secret = $dh->getSharedSecretKey();

        $params = array(
            "openid.ns" => "http://specs.openid.net/auth/2.0",
            "openid.assoc_type" => OpenIdProtocol::SignatureAlgorithmHMAC_SHA256,
            "openid.dh_consumer_public" => $rp_public_key,
            "openid.mode" => 'associate',
            "openid.session_type" => OpenIdProtocol::AssociationSessionTypeDHSHA256,
        );


        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $params);
        $body = $response->getContent();
        $lines = explode("\n", $body);
        $params = array();
        foreach ($lines as $line) {
            if (empty($line)) continue;
            $param = explode(":", $line, 2);
            $params[$param[0]] = $param[1];
        }
        $this->assertResponseStatus(200);
        $this->assertTrue(isset($params[OpenIdProtocol::OpenIDProtocol_NS]) && $params[OpenIdProtocol::OpenIDProtocol_NS] == OpenIdProtocol::OpenID2MessageType);
        $this->assertTrue(isset($params[OpenIdProtocol::OpenIDProtocol_AssocType]) && $params[OpenIdProtocol::OpenIDProtocol_AssocType] == OpenIdProtocol::SignatureAlgorithmHMAC_SHA256);
        $this->assertTrue(isset($params[OpenIdProtocol::OpenIDProtocol_SessionType]) && $params[OpenIdProtocol::OpenIDProtocol_SessionType] == OpenIdProtocol::AssociationSessionTypeDHSHA256);
    }


} 