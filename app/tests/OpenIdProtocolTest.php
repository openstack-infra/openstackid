<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 4:20 PM
 * To change this template use File | Settings | File Templates.
 */

use openid\OpenIdProtocol;

class OpenIdProtocolTest extends TestCase {

    public function testProtocolIdpDiscovery(){
        $protocol = App::make("openid\OpenIdProtocol");
        $xrds = $protocol->getXRDSDiscovery();
        $this->assertTrue(!empty($xrds) && str_contains($xrds,"http://specs.openid.net/auth/2.0/server") && str_contains($xrds,"http://openid.net/srv/ax/1.0") && str_contains($xrds,"http://specs.openid.net/extensions/pape/1.0"));
    }
}