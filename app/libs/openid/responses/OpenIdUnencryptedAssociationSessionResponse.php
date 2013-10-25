<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/25/13
 * Time: 6:59 PM
 */

namespace openid\responses;


class OpenIdUnencryptedAssociationSessionResponse extends OpenIdAssociationSessionResponse {
    public function __construct($assoc_handle,$session_type, $assoc_type,$expires_in){
        parent::__construct($assoc_handle,$session_type, $assoc_type,$expires_in);
    }
} 