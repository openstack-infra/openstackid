<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/25/13
 * Time: 7:01 PM
 */

namespace openid\responses;

/**
 * Class OpenIdDiffieHellmanAssociationSessionResponse
 * @package openid\responses
 */
class OpenIdDiffieHellmanAssociationSessionResponse extends OpenIdAssociationSessionResponse{

    public function __construct($assoc_handle,$session_type, $assoc_type,$expires_in){
        parent::__construct($assoc_handle,$session_type, $assoc_type,$expires_in);
    }
} 