<?php

class DiscoveryControllerTest extends TestCase
{

    public function testIdpDiscovery()
    {
        $response = $this->action("GET", "OpenId\DiscoveryController@idp");
        //"application/xrds+xml"
        $this->assertTrue($response->getStatusCode() === 200);
    }
}