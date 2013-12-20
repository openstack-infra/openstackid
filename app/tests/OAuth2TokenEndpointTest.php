<?php

use auth\OpenIdUser;
use utils\services\IAuthService;

/**
 * Class OAuth2TokenEndpointTest
 */
class OAuth2TokenEndpointTest extends TestCase {

    /**
     * Get Auth Code Test
     */
    public function testAuthCode(){

        $client_id     = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';

        $params = array(
            'client_id'        => $client_id,
            'redirect_uri'     => 'https://www.test.com/oauth2',
            'response_type'    => 'code',
            'scope'            => 'https://www.test.com/users/activities.read'
        );

        $user = OpenIdUser::where('external_id','=','smarcet@gmail.com')->first();

        Auth::login($user);

        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        $response = $this->action("POST", "OAuth2ProviderController@authorize",
            $params,
            array(),
            array(),
            array());

        $status   = $response->getStatusCode();
        $url      = $response->getTargetUrl();
        $content  = $response->getContent();
    }

    /**
     * Get Token Test
     */
    public function testToken(){


        $client_id     = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $client_secret = 'ITc/6Y5N7kOtGKhg';

        $params = array(
            'client_id'        =>$client_id,
            'redirect_uri'     => 'https://www.test.com/oauth2',
            'response_type'    => 'code',
            'scope'            => 'https://www.test.com/users/activities.read'
        );

        $user = OpenIdUser::where('external_id','=','smarcet@gmail.com')->first();

        Auth::login($user);

        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        $response = $this->action("POST", "OAuth2ProviderController@authorize",
            $params,
            array(),
            array(),
            array());

        $status   = $response->getStatusCode();
        $url      = $response->getTargetUrl();
        $content  = $response->getContent();

        $comps = @parse_url($url);
        $query = $comps['query'];
        $output = array();
        parse_str($query, $output);

        $params = array(
            'code'          => $output['code'],
            'redirect_uri'  => 'https://www.test.com/oauth2',
            'grant_type'    => 'authorization_code',
        );



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