<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 6:22 PM
 * To change this template use File | Settings | File Templates.
 */

class OpenIdProviderControllerTest extends TestCase {

    public function testOpenIdRequest(){
        $params = array(
            "openid.ns"=>"http://specs.openid.net/auth/2.0",
            "openid.claimed_id"=>"http://specs.openid.net/auth/2.0/identifier_select",
            "openid.identity"=>"http://specs.openid.net/auth/2.0/identifier_select",
            "openid.return_to"=>"http://www.test.com",
            "openid.realm"=>"http://www.test.com/",
            "openid.mode"=>"checkid_setup"
        );

        $response = $this->client->request("POST","/accounts/openid/v2",$params);
    }
}