<?php

use auth\User;
use oauth2\OAuth2Protocol;
use utils\services\IAuthService;

/**
 * Class OAuth2ProtocolTest
 * Test Suite for OAuth2 Protocol
 */
class OAuth2ProtocolTest extends OpenStackIDBaseTest
{

    private $current_realm;

	protected function prepareForTests()
	{
		parent::prepareForTests();
		//Route::enableFilters();
		$this->current_realm = Config::get('app.url');
		$user = User::where('external_id', '=', 'smarcet@gmail.com')->first();
		$this->be($user);
		Session::start();
	}

    /**
     * Get Auth Code Test
     */
    public function testAuthCode()
    {

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';

        $params = array(
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope' => sprintf('%s/resource-server/read', $this->current_realm),
        );


        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        $response = $this->action("POST", "OAuth2ProviderController@authorize",
            $params,
            array(),
            array(),
            array());

        $url = $response->getTargetUrl();
        $content = $response->getContent();

        $this->assertResponseStatus(302);
    }

    /** Get Token Test
     * @throws Exception
     */
    public function testToken()
    {

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $client_secret = 'ITc/6Y5N7kOtGKhg';

        $params = array(
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => OAuth2Protocol::OAuth2Protocol_ResponseType_Code,
            'scope' => sprintf('%s/resource-server/read', $this->current_realm),
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
            array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

        $status = $response->getStatusCode();

        $this->assertResponseStatus(200);

        $content = $response->getContent();

        $response = json_decode($content);
        $access_token = $response->access_token;
        $refresh_token = $response->refresh_token;

        $this->assertTrue(!empty($access_token));
        $this->assertTrue(!empty($refresh_token));


    }

    /** test validate token grant
     * @throws Exception
     */
    public function testValidateToken()
    {

        try {

            $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
            $client_secret = 'ITc/6Y5N7kOtGKhg';

            Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

            //do authorization ...

            $params = array(
                'client_id' => $client_id,
                'redirect_uri' => 'https://www.test.com/oauth2',
                'response_type' => OAuth2Protocol::OAuth2Protocol_ResponseType_Code,
                OAuth2Protocol::OAuth2Protocol_AccessType =>OAuth2Protocol::OAuth2Protocol_AccessType_Offline,
                'scope' => sprintf('%s/resource-server/read', $this->current_realm),
            );

            $response = $this->action("POST", "OAuth2ProviderController@authorize",
                $params,
                array(),
                array(),
                array());

            $status = $response->getStatusCode();
            $url = $response->getTargetUrl();
            $content = $response->getContent();

            // get auth code ...
            $comps = @parse_url($url);
            $query = $comps['query'];
            $output = array();
            parse_str($query, $output);


            //do get auth token...
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
                array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

            $this->assertResponseStatus(200);

            $content = $response->getContent();

            $response = json_decode($content);
            //get access token and refresh token...
            $access_token = $response->access_token;
            $refresh_token = $response->refresh_token;

            $this->assertTrue(!empty($access_token));
            $this->assertTrue(!empty($refresh_token));

            //do token validation ....
            $params = array(
                'token' => $access_token,
            );

            $response = $this->action("POST", "OAuth2ProviderController@introspection",
                $params,
                array(),
                array(),
                // Symfony interally prefixes headers with "HTTP", so
                array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

            $this->assertResponseStatus(200);

            $content = $response->getContent();

            $response = json_decode($content);
            $validate_access_token = $response->access_token;
            //old token and new token should be equal
            $this->assertTrue(!empty($validate_access_token));
            $this->assertTrue($validate_access_token === $access_token);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /** test refresh token grant
     * @throws Exception
     */
    public function testRefreshToken()
    {
        try {

            $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
            $client_secret = 'ITc/6Y5N7kOtGKhg';


            Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

            //do authorization ...

            $params = array(
                OAuth2Protocol::OAuth2Protocol_ClientId => $client_id,
                OAuth2Protocol::OAuth2Protocol_RedirectUri => 'https://www.test.com/oauth2',
                OAuth2Protocol::OAuth2Protocol_ResponseType => OAuth2Protocol::OAuth2Protocol_ResponseType_Code,
                OAuth2Protocol::OAuth2Protocol_Scope => sprintf('%s/resource-server/read', $this->current_realm),
                OAuth2Protocol::OAuth2Protocol_AccessType =>OAuth2Protocol::OAuth2Protocol_AccessType_Offline
            );

            $response = $this->action("POST", "OAuth2ProviderController@authorize",
                $params,
                array(),
                array(),
                array());

            $status = $response->getStatusCode();
            $url = $response->getTargetUrl();
            $content = $response->getContent();

            // get auth code ...
            $comps = @parse_url($url);
            $query = $comps['query'];
            $output = array();
            parse_str($query, $output);


            //do get auth token...
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
                array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

            $this->assertResponseStatus(200);

            $content = $response->getContent();

            $response = json_decode($content);
            //get access token and refresh token...
            $access_token = $response->access_token;
            $refresh_token = $response->refresh_token;

            $this->assertTrue(!empty($access_token));
            $this->assertTrue(!empty($refresh_token));


            $params = array(
                'refresh_token' => $refresh_token,
                'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_RefreshToken,
            );

            $response = $this->action("POST", "OAuth2ProviderController@token",
                $params,
                array(),
                array(),
                // Symfony interally prefixes headers with "HTTP", so
                array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

            $this->assertResponseStatus(200);

            $content = $response->getContent();

            $response = json_decode($content);

            //get new access token and new refresh token...
            $new_access_token = $response->access_token;
            $new_refresh_token = $response->refresh_token;

            $this->assertTrue(!empty($new_access_token));
            $this->assertTrue(!empty($new_refresh_token));

        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * test refresh token replay attack
     * @throws Exception
     */
    public function testRefreshTokenReplayAttack()
    {
        try {

            $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
            $client_secret = 'ITc/6Y5N7kOtGKhg';

            Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

            //do authorization ...

            $params = array(
                'client_id' => $client_id,
                'redirect_uri' => 'https://www.test.com/oauth2',
                'response_type' => OAuth2Protocol::OAuth2Protocol_ResponseType_Code,
                'scope' => sprintf('%s/resource-server/read', $this->current_realm),
                OAuth2Protocol::OAuth2Protocol_AccessType =>OAuth2Protocol::OAuth2Protocol_AccessType_Offline
            );

            $response = $this->action("POST", "OAuth2ProviderController@authorize",
                $params,
                array(),
                array(),
                array());

            $status = $response->getStatusCode();
            $url = $response->getTargetUrl();
            $content = $response->getContent();

            // get auth code ...
            $comps = @parse_url($url);
            $query = $comps['query'];
            $output = array();
            parse_str($query, $output);


            //do get auth token...
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
                array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

            $this->assertResponseStatus(200);

            $content = $response->getContent();

            $response = json_decode($content);
            //get access token and refresh token...
            $access_token = $response->access_token;
            $refresh_token = $response->refresh_token;

            $this->assertTrue(!empty($access_token));
            $this->assertTrue(!empty($refresh_token));


            $params = array(
                'refresh_token' => $refresh_token,
                'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_RefreshToken,
            );

            $response = $this->action("POST", "OAuth2ProviderController@token",
                $params,
                array(),
                array(),
                // Symfony interally prefixes headers with "HTTP", so
                array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

            $this->assertResponseStatus(200);

            $content = $response->getContent();

            $response = json_decode($content);

            //get new access token and new refresh token...
            $new_access_token = $response->access_token;
            $new_refresh_token = $response->refresh_token;

            $this->assertTrue(!empty($new_access_token));
            $this->assertTrue(!empty($new_refresh_token));

            //do re refresh and we will get a 400 http error ...
            $response = $this->action("POST", "OAuth2ProviderController@token",
                $params,
                array(),
                array(),
                // Symfony interally prefixes headers with "HTTP", so
                array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

            $this->assertResponseStatus(400);

        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function testImplicitFlow()
    {

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwKlfSyQ3x.openstack.client';

        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        $params = array(
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => OAuth2Protocol::OAuth2Protocol_ResponseType_Token,
            'scope' => sprintf('%s/resource-server/read', $this->current_realm),
            'state' => '123456'
        );

        $response = $this->action("POST", "OAuth2ProviderController@authorize",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);
        $url = $response->getTargetUrl();
        // get auth code ...
        $comps = @parse_url($url);
        $fragment = $comps['fragment'];
        $response = array();
        parse_str($fragment, $response);

        $this->assertTrue(isset($response['access_token']) && !empty($response['access_token']));
        $this->assertTrue(isset($response['expires_in']));
        $this->assertTrue(isset($response['scope']));
        $this->assertTrue(isset($response['state']));
        $this->assertTrue($response['state'] === '123456');
        $this->assertTrue(isset($response['token_type']));
        $this->assertTrue($response['token_type'] === 'Bearer');

    }

    public function testTokenRevocation()
    {
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwKlfSyQ3x.openstack.client';

        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        $params = array(
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => OAuth2Protocol::OAuth2Protocol_ResponseType_Token,
            'scope' => sprintf('%s/resource-server/read', $this->current_realm),
            OAuth2Protocol::OAuth2Protocol_AccessType =>OAuth2Protocol::OAuth2Protocol_AccessType_Offline,
            'state' => '123456'
        );

        $response = $this->action("POST", "OAuth2ProviderController@authorize",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);
        $url = $response->getTargetUrl();
        // get auth code ...
        $comps = @parse_url($url);
        $fragment = $comps['fragment'];
        $response = array();
        parse_str($fragment, $response);

        $this->assertTrue(isset($response['access_token']) && !empty($response['access_token']));
        $this->assertTrue(isset($response['expires_in']));
        $this->assertTrue(isset($response['scope']));
        $this->assertTrue(isset($response['state']));
        $this->assertTrue($response['state'] === '123456');
        $this->assertTrue(isset($response['token_type']));
        $this->assertTrue($response['token_type'] === 'Bearer');


        $params = array(
            OAuth2Protocol::OAuth2Protocol_Token => $response['access_token'],
            OAuth2Protocol::OAuth2Protocol_TokenType_Hint => OAuth2Protocol::OAuth2Protocol_AccessToken,
            OAuth2Protocol::OAuth2Protocol_ClientId => $client_id
        );

        $response = $this->action("POST", "OAuth2ProviderController@revoke",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(200);

    }

    public function testTokenRevocationInvalidClient()
    {
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwKlfSyQ3x.openstack.client';

        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        $params = array(
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => OAuth2Protocol::OAuth2Protocol_ResponseType_Token,
            'scope' => sprintf('%s/resource-server/read', $this->current_realm),
            OAuth2Protocol::OAuth2Protocol_AccessType =>OAuth2Protocol::OAuth2Protocol_AccessType_Offline,
            'state' => '123456'
        );

        $response = $this->action("POST", "OAuth2ProviderController@authorize",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);
        $url = $response->getTargetUrl();
        // get auth code ...
        $comps = @parse_url($url);
        $fragment = $comps['fragment'];
        $response = array();
        parse_str($fragment, $response);

        $this->assertTrue(isset($response['access_token']) && !empty($response['access_token']));
        $this->assertTrue(isset($response['expires_in']));
        $this->assertTrue(isset($response['scope']));
        $this->assertTrue(isset($response['state']));
        $this->assertTrue($response['state'] === '123456');
        $this->assertTrue(isset($response['token_type']));
        $this->assertTrue($response['token_type'] === 'Bearer');

        //set another public client
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwKlfSyQ2x.openstack.client';
        $params = array(
            OAuth2Protocol::OAuth2Protocol_Token => $response['access_token'],
            OAuth2Protocol::OAuth2Protocol_TokenType_Hint => OAuth2Protocol::OAuth2Protocol_AccessToken,
            OAuth2Protocol::OAuth2Protocol_ClientId => $client_id
        );

        $response = $this->action("POST", "OAuth2ProviderController@revoke",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(200);
    }

    public function testTokenRevocationInvalidHint()
    {

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwKlfSyQ3x.openstack.client';

        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        $params = array(
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => OAuth2Protocol::OAuth2Protocol_ResponseType_Token,
            'scope' => sprintf('%s/resource-server/read', $this->current_realm),
            OAuth2Protocol::OAuth2Protocol_AccessType =>OAuth2Protocol::OAuth2Protocol_AccessType_Offline,
            'state' => '123456'
        );

        $response = $this->action("POST", "OAuth2ProviderController@authorize",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);
        $url = $response->getTargetUrl();
        // get auth code ...
        $comps = @parse_url($url);
        $fragment = $comps['fragment'];
        $response = array();
        parse_str($fragment, $response);

        $this->assertTrue(isset($response['access_token']) && !empty($response['access_token']));
        $this->assertTrue(isset($response['expires_in']));
        $this->assertTrue(isset($response['scope']));
        $this->assertTrue(isset($response['state']));
        $this->assertTrue($response['state'] === '123456');
        $this->assertTrue(isset($response['token_type']));
        $this->assertTrue($response['token_type'] === 'Bearer');


        $params = array(
            OAuth2Protocol::OAuth2Protocol_Token => $response['access_token'],
            OAuth2Protocol::OAuth2Protocol_TokenType_Hint => OAuth2Protocol::OAuth2Protocol_RefreshToken,
            OAuth2Protocol::OAuth2Protocol_ClientId => $client_id
        );

        $response = $this->action("POST", "OAuth2ProviderController@revoke",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(200);

    }

    public function testTokenRevocationInvalidToken()
    {

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwKlfSyQ3x.openstack.client';

        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        $params = array(
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => OAuth2Protocol::OAuth2Protocol_ResponseType_Token,
            'scope' => sprintf('%s/resource-server/read', $this->current_realm),
            OAuth2Protocol::OAuth2Protocol_AccessType =>OAuth2Protocol::OAuth2Protocol_AccessType_Offline,
            'state' => '123456'
        );

        $response = $this->action("POST", "OAuth2ProviderController@authorize",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);
        $url = $response->getTargetUrl();
        // get auth code ...
        $comps = @parse_url($url);
        $fragment = $comps['fragment'];
        $response = array();
        parse_str($fragment, $response);

        $this->assertTrue(isset($response['access_token']) && !empty($response['access_token']));
        $this->assertTrue(isset($response['expires_in']));
        $this->assertTrue(isset($response['scope']));
        $this->assertTrue(isset($response['state']));
        $this->assertTrue($response['state'] === '123456');
        $this->assertTrue(isset($response['token_type']));
        $this->assertTrue($response['token_type'] === 'Bearer');


        $params = array(
            OAuth2Protocol::OAuth2Protocol_Token => '12345678910',
            OAuth2Protocol::OAuth2Protocol_ClientId => $client_id
        );

        $response = $this->action("POST", "OAuth2ProviderController@revoke",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(200);
    }

    public function testClientCredentialsFlow()
    {
        try {

            $client_id = '11z87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
            $client_secret = '11c/6Y5N7kOtGKhg';

            //do get auth token...
            $params = array(
                OAuth2Protocol::OAuth2Protocol_GrantType => OAuth2Protocol::OAuth2Protocol_GrantType_ClientCredentials,
                OAuth2Protocol::OAuth2Protocol_Scope => sprintf('%s/resource-server/read', $this->current_realm),
            );

            $response = $this->action("POST", "OAuth2ProviderController@token",
                $params,
                array(),
                array(),
                // Symfony interally prefixes headers with "HTTP", so
                array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

            $this->assertResponseStatus(200);

            $content = $response->getContent();

            $response = json_decode($content);

            $this->assertTrue(!empty($response->access_token));

        } catch (Exception $ex) {
            throw $ex;
        }

    }
}