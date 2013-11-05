<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/25/13
 * Time: 7:01 PM
 */

namespace openid\responses;

use openid\OpenIdProtocol;

/**
 * Class OpenIdDiffieHellmanAssociationSessionResponse
 * @package openid\responses
 */
class OpenIdDiffieHellmanAssociationSessionResponse extends OpenIdAssociationSessionResponse
{

    public function __construct($assoc_handle, $session_type, $assoc_type, $expires_in, $server_public, $enc_mac_key)
    {
        parent::__construct($assoc_handle, $session_type, $assoc_type, $expires_in);
        $this[OpenIdProtocol::OpenIdProtocol_DHServerPublic] = $server_public;
        $this[OpenIdProtocol::OpenIdProtocol_DHEncMacKey] = $enc_mac_key;
    }
} 