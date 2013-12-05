<?php

class OAuth2TokenEndpointTest extends TestCase {

    public function testToken(){

        $params = array(
            'code'          => 'cT/p/XfALZhmoAgMEcfGScOe1Eg8bVuL',
            'redirect_uri'  => 'https://developers.google.com/oauthplayground',
            'grant_type'    => 'authorization_code',
            'client_id'     => '1',
            'client_secret' => '1'
        );

        $response = $this->action("POST", "OAuth2ProviderController@token", $params);
        $status   = $response->getStatusCode();
        $content  = $response->getContent();
    }
} 