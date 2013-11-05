<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/28/13
 * Time: 7:18 PM
 */

namespace openid\responses;

use openid\OpenIdProtocol;

class OpenIdAssociationSessionUnsuccessfulResponse extends OpenIdDirectResponse
{

    public function __construct($error)
    {
        parent::__construct();
        $this[OpenIdProtocol::OpenIDProtocol_Error] = $error;
        $this[OpenIdProtocol::OpenIDProtocol_ErrorCode] = 'unsupported-type';
        $this[OpenIdProtocol::OpenIDProtocol_SessionType] = OpenIdProtocol::AssociationSessionTypeDHSHA256;
        $this[OpenIdProtocol::OpenIDProtocol_AssocType] = OpenIdProtocol::SignatureAlgorithmHMAC_SHA256;
    }
} 