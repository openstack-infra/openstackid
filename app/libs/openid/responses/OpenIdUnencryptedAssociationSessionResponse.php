<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/25/13
 * Time: 6:59 PM
 */

namespace openid\responses;

use openid\OpenIdProtocol;

class OpenIdUnencryptedAssociationSessionResponse extends OpenIdAssociationSessionResponse
{

    /**
     * @param $assoc_handle
     * @param $session_type
     * @param $assoc_type
     * @param $expires_in
     * @param $secret
     */
    public function __construct($assoc_handle, $session_type, $assoc_type, $expires_in, $secret)
    {
        parent::__construct($assoc_handle, $session_type, $assoc_type, $expires_in);
        $this[OpenIdProtocol::OpenIdProtocol_MacKey] = base64_decode($secret);
    }
} 