<?php

namespace openid\requests;

use openid\exceptions\InvalidDHParam;
use openid\helpers\OpenIdCryptoHelper;
use openid\helpers\OpenIdErrorMessages;
use openid\OpenIdMessage;
use openid\OpenIdProtocol;
use Zend\Crypt\PublicKey\DiffieHellman;

class OpenIdDHAssociationSessionRequest extends OpenIdAssociationSessionRequest
{


    // Default Diffie-Hellman key generator (1024 bit)
    const DH_P = 'DCF93A0B883972EC0E19989AC5A2CE310E1D37717E8D9571BB7623731866E61EF75A2E27898B057F9891C2E27A639C3F29B60814581CD3B2CA3986D2683705577D45C2E7E52DC81C7A171876E5CEA74B1448BFDFAF18828EFD2519F14E45E3826634AF1949E5B535CC829A483B8A76223E5D490A257F05BDFF16F2FB22C583AB';
    // Default Diffie-Hellman prime number (should be 2 or 5)
    const DH_G = '02';
    private $p_number;
    private $g_number;
    private $rp_pub_key;

    public function __construct(OpenIdMessage $message)
    {
        parent::__construct($message);
        $this->g_number = null;
        $this->p_number = null;
        $this->rp_pub_key = null;
    }

    /**
     * @param OpenIdMessage $message
     * @return bool
     */
    public static function IsOpenIdDHAssociationSessionRequest(OpenIdMessage $message)
    {
        if (OpenIdAssociationSessionRequest::IsOpenIdAssociationSessionRequest($message)) {
            $session_type = $message->getParam(OpenIdProtocol::OpenIDProtocol_AssocType);
            if ($session_type == OpenIdProtocol::AssociationSessionTypeDHSHA1 || $session_type == OpenIdProtocol::AssociationSessionTypeDHSHA256)
                return true;
        }
        return false;
    }

    /**
     * @return bool
     * @throws InvalidDHParam
     * @throws \openid\exceptions\InvalidSessionTypeException
     * @throws \openid\exceptions\InvalidAssociationTypeException
     */
    public function IsValid()
    {
        $res = parent::IsValid();
        if (!$res) return false;
        $dh_modulus = $this->getDHModulus();
        $dh_gen = $this->getDHGen();
        $dh_consumer_public = $this->getDHConsumerPublic();
        if (!empty($dh_modulus) && !empty($dh_gen) && !empty($dh_consumer_public))
            return true;
        return true;
    }

    /**
     * @return null|string
     * @throws \openid\exceptions\InvalidDHParam
     */
    public function getDHModulus()
    {
        if (is_null($this->p_number)) {
            $p_param = $this->getParam(OpenIdProtocol::OpenIdProtocol_DHModulus);
            $p_bin = empty($p_param) ? pack('H*', OpenIdDHAssociationSessionRequest::DH_P) : base64_decode($p_param);
            if (!$p_bin)
                throw new InvalidDHParam(sprintf(OpenIdErrorMessages::InvalidDHParamMessage, OpenIdProtocol::OpenIdProtocol_DHModulus));
            $this->p_number = OpenIdCryptoHelper::convert($p_bin, DiffieHellman::FORMAT_BINARY, DiffieHellman::FORMAT_NUMBER);
        }
        return $this->p_number;
    }

    /**
     * @return null|string
     * @throws \openid\exceptions\InvalidDHParam
     */
    public function getDHGen()
    {
        if (is_null($this->g_number)) {
            $g_param = $this->getParam(OpenIdProtocol::OpenIdProtocol_DHGen);
            $g_bin = empty($g_param) ? pack('H*', OpenIdDHAssociationSessionRequest::DH_G) : base64_decode($g_param);
            if (!$g_bin)
                throw new InvalidDHParam(sprintf(OpenIdErrorMessages::InvalidDHParamMessage, OpenIdProtocol::OpenIdProtocol_DHGen));
            $this->g_number = OpenIdCryptoHelper::convert($g_bin, DiffieHellman::FORMAT_BINARY, DiffieHellman::FORMAT_NUMBER);
        }
        return $this->g_number;
    }

    /**
     * @return null|string
     * @throws \openid\exceptions\InvalidDHParam
     */
    public function getDHConsumerPublic()
    {
        if (is_null($this->rp_pub_key)) {
            $rp_pub_key_param = $this->getParam(OpenIdProtocol::OpenIdProtocol_DHConsumerPublic);
            if (empty($rp_pub_key_param))
                throw new InvalidDHParam(sprintf(OpenIdErrorMessages::InvalidDHParamMessage, OpenIdProtocol::OpenIdProtocol_DHConsumerPublic));
            $rp_pub_key_bin = base64_decode($rp_pub_key_param);
            if (!$rp_pub_key_bin)
                throw new InvalidDHParam(sprintf(OpenIdErrorMessages::InvalidDHParamMessage, OpenIdProtocol::OpenIdProtocol_DHConsumerPublic));
            $this->rp_pub_key = OpenIdCryptoHelper::convert($rp_pub_key_bin, DiffieHellman::FORMAT_BINARY, DiffieHellman::FORMAT_NUMBER);
        }
        return $this->rp_pub_key;
    }

} 