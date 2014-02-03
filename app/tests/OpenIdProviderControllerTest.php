<?php

class OpenIdProviderControllerTest extends TestCase
{

    public function testOpenIdRequest()
    {
        $params = array(
            "openid.ns" => "http://specs.openid.net/auth/2.0",
            "openid.claimed_id" => "http://specs.openid.net/auth/2.0/identifier_select",
            "openid.identity" => "http://specs.openid.net/auth/2.0/identifier_select",
            "openid.return_to" => "http://www.test.com",
            "openid.realm" => "http://www.test.com/",
            "openid.mode" => "checkid_setup"
        );

        $response = $this->action("POST", "OpenIdProviderController@endpoint", $params);
    }
}