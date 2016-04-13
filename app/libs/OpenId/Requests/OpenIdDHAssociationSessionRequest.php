<?php namespace OpenId\Requests;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use OpenId\Helpers\OpenIdCryptoHelper;
use OpenId\Helpers\OpenIdErrorMessages;
use OpenId\OpenIdMessage;
use OpenId\OpenIdProtocol;
use OpenId\Exceptions\InvalidAssociationTypeException;
use OpenId\Exceptions\InvalidSessionTypeException;
use OpenId\Exceptions\InvalidDHParam;
use Zend\Crypt\PublicKey\DiffieHellman;
/**
 * Class OpenIdDHAssociationSessionRequest
 * @package OpenId\Requests
 */
class OpenIdDHAssociationSessionRequest extends OpenIdAssociationSessionRequest
{


    // Default Diffie-Hellman key generator (1024 bit)
    const DH_P = 'DCF93A0B883972EC0E19989AC5A2CE310E1D37717E8D9571BB7623731866E61EF75A2E27898B057F9891C2E27A639C3F29B60814581CD3B2CA3986D2683705577D45C2E7E52DC81C7A171876E5CEA74B1448BFDFAF18828EFD2519F14E45E3826634AF1949E5B535CC829A483B8A76223E5D490A257F05BDFF16F2FB22C583AB';
    // Default Diffie-Hellman prime number (should be 2 or 5)
    const DH_G = '02';
    private $p_number;
    private $g_number;
    private $rp_pub_key;

    /**
     * OpenIdDHAssociationSessionRequest constructor.
     * @param OpenIdMessage $message
     */
    public function __construct(OpenIdMessage $message)
    {
        parent::__construct($message);
        $this->g_number   = null;
        $this->p_number   = null;
        $this->rp_pub_key = null;
    }

    /**
     * @param OpenIdMessage $message
     * @return bool
     */
    public static function IsOpenIdDHAssociationSessionRequest(OpenIdMessage $message)
    {
        if (OpenIdAssociationSessionRequest::IsOpenIdAssociationSessionRequest($message)) {
            $session_type = $message->getParam(OpenIdProtocol::OpenIDProtocol_SessionType);
            if ($session_type == OpenIdProtocol::AssociationSessionTypeDHSHA1 || $session_type == OpenIdProtocol::AssociationSessionTypeDHSHA256)
                return true;
        }
        return false;
    }

    /**
     * @return bool
     * @throws InvalidDHParam
     * @throws InvalidAssociationTypeException
     * @throws InvalidSessionTypeException
     */
    public function isValid()
    {
        $res = parent::isValid();
        if (!$res) return false;
        $dh_modulus         = $this->getDHModulus();
        $dh_gen             = $this->getDHGen();
        $dh_consumer_public = $this->getDHConsumerPublic();

        if (empty($dh_modulus) || empty($dh_gen) || empty($dh_consumer_public))
            return false;

        if (!preg_match('/^\d+$/', $dh_modulus) || $dh_modulus  < 11) {
            return false;
        }
        // not a positive natural number greater than 1 ...
        if (!preg_match('/^\d+$/', $dh_gen) || $dh_gen < 2) {
           return false;
        }

        return true;
    }

    /**
     * @return null|string
     * @throws InvalidDHParam
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
     * @throws InvalidDHParam
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
     * @throws InvalidDHParam
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