<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/3/13
 * Time: 5:25 PM
 */

namespace oauth2\responses;


use oauth2\OAuth2Protocol;
use openid\responses\OpenIdIndirectResponse;

class OAuth2ErrorResponse extends OpenIdIndirectResponse {

    public function setError($error){
        $this[OAuth2Protocol::OAuth2Protocol_Error] = $error;
    }
} 