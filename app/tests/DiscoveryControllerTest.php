<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 12:41 PM
 * To change this template use File | Settings | File Templates.
 */

class DiscoveryControllerTest extends TestCase {

    public function testIdpDiscovery(){
        $response = $this->call('GET', '/discovery');
        //"application/xrds+xml"
        $this->assertTrue($response->getStatusCode()===200 );
    }
}