<?php

use oauth2\resource_server\IUserService;
use oauth2\OAuth2Protocol;
use auth\User;
use utils\services\IAuthService;

/**
 * Class OAuth2UserServiceApiTest
 */
class OAuth2UserServiceApiTest  extends TestCase {

    private $access_token;
    private $client_id;
    private $client_secret;
    private $current_realm;

    protected function prepareForTests()
    {
        parent::prepareForTests();
        Route::enableFilters();

        $this->current_realm = Config::get('app.url');

	    $user = User::where('external_id', '=', 'smarcet@gmail.com')->first();

	    $this->be($user);

	    Session::start();

        $scope = array(
            IUserService::UserProfileScope_Address,
            IUserService::UserProfileScope_Email,
            IUserService::UserProfileScope_Profile
        );

        $this->client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $this->client_secret = 'ITc/6Y5N7kOtGKhg';

        $params = array(
            'client_id' => $this->client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => OAuth2Protocol::OAuth2Protocol_ResponseType_Code,
            'scope' => implode(' ',$scope),
            OAuth2Protocol::OAuth2Protocol_AccessType =>OAuth2Protocol::OAuth2Protocol_AccessType_Offline,
        );


        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        $response = $this->action("POST", "OAuth2ProviderController@authorize",
            $params,
            array(),
            array(),
            array());

        $status = $response->getStatusCode();
        $url = $response->getTargetUrl();
        $content = $response->getContent();

        $comps = @parse_url($url);
        $query = $comps['query'];
        $output = array();
        parse_str($query, $output);

        $params = array(
            'code' => $output['code'],
            'redirect_uri' => 'https://www.test.com/oauth2',
            'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
        );

        $response = $this->action("POST", "OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            // Symfony interally prefixes headers with "HTTP", so
            array("HTTP_Authorization" => " Basic " . base64_encode($this->client_id . ':' . $this->client_secret)));

        $status = $response->getStatusCode();

        $this->assertResponseStatus(200);

        $content = $response->getContent();

        $response = json_decode($content);
        $access_token = $response->access_token;
        $refresh_token = $response->refresh_token;

        $this->access_token = $access_token;
    }

    /**
     * @covers OAuth2UserApiController::get()
     */
    public function testGetInfo(){
        $response = $this->action("GET", "OAuth2UserApiController@me",
            array(),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $this->assertResponseStatus(200);
        $content   = $response->getContent();
        $user_info = json_decode($content);
    }

    public function testGetInfoCORS(){
        $response = $this->action("OPTION", "OAuth2UserApiController@me",
            array(),
            array(),
            array(),
            array(
                "HTTP_Authorization" => " Bearer " .$this->access_token,
                'HTTP_Origin' => array('www.test.com','www.test1.com'),
                'HTTP_Access-Control-Request-Method'=>'GET',
            ));

        $this->assertResponseStatus(403);
        $content   = $response->getContent();
        $user_info = json_decode($content);
    }
}