<?php

class OAuth2TokenEndpointTest extends TestCase {

    public function testAuthCode(){
        $params = array(
            'client_id'        =>'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client',
            'redirect_uri'     => 'https://developers.google.com/oauthplayground?test=1&test=2',
            'response_type'    => 'code',
            'scope'            => 'https://www.test.com/users/activities.read https://www.test.com/users/activities.write'
        );

        $response = $this->action("POST", "OAuth2ProviderController@authorize",
            $params,
            array(),
            array(),
            array());
        $status   = $response->getStatusCode();
        $content  = $response->getContent();
    }

    public function testToken(){

        $params = array(
            'code'          => 'KSu6nBO2WCm66myvxcctxGM4niry6KuU',
            'redirect_uri'  => 'https://developers.google.com/oauthplayground',
            'grant_type'    => 'authorization_code',
        );

        $client_id     = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $client_secret = 'ITc/6Y5N7kOtGKhg';

        $response = $this->action("POST", "OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            // Symfony interally prefixes headers with "HTTP", so
            array("HTTP_Authorization"=>" Basic ".base64_encode($client_id.':'.$client_secret)));
        $status   = $response->getStatusCode();
        $content  = $response->getContent();
    }
} 