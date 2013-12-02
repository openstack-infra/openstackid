<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/2/13
 * Time: 3:26 PM
 */

namespace oauth2;
use oauth2\requests\OAuth2Request;

interface IOAuth2Protocol {
    public function authorize(OAuth2Request $request);
    public function token(OAuth2Request $request);
} 