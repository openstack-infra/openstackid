<?php

class DiscoveryControllerTest extends TestCase
{

    public function testIdpDiscovery()
    {
        $response = $this->call('GET', '/discovery');
        //"application/xrds+xml"
        $this->assertTrue($response->getStatusCode() === 200);
    }
}