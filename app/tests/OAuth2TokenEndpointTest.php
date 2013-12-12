<?php

class OAuth2TokenEndpointTest extends TestCase {

    public function testToken(){

        $params = array(
            'code'          => '5wXwH623NLJ+gXz7BUk+zrUuVB1mN1vX',
            'redirect_uri'  => 'https://developers.google.com/oauthplayground',
            'grant_type'    => 'authorization_code',
        );

        $client_id     = '1';
        $client_secret = '44FuVlIL8qA8YISg';
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