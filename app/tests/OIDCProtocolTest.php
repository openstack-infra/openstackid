<?php

/**
 * Copyright 2015 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use auth\User;
use Illuminate\Support\Facades\App;
use oauth2\OAuth2Protocol;
use services\utils\ServerConfigurationService;
use utils\services\IAuthService;
use utils\services\UtilsServiceCatalog;

class OIDCProtocolTest extends OpenStackIDBaseTest
{
    private $current_realm;

    protected function prepareForTests()
    {
        parent::prepareForTests();
        App::singleton(UtilsServiceCatalog::ServerConfigurationService, 'StubServerConfigurationService');
        $this->current_realm = Config::get('app.url');
        $user = User::where('identifier', '=', 'sebastian.marcet')->first();
        $this->be($user);
        Session::start();
    }

    /**
     * Get Auth Code Test
     */
    public function testAuthCode()
    {

        Route::enableFilters();

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';

        $params = array(
            'client_id'    => $client_id,
            'redirect_uri'  => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope'         => 'openid profile email'
        );

        $response = $this->action("POST", "OAuth2ProviderController@authorize",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();

        $consent_response = $this->call('POST', $url,
            array
            (
                'trust'  => 'AllowOnce',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $auth_response =$this->action("GET", "OAuth2ProviderController@authorize",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $auth_response->getTargetUrl();

        $comps = @parse_url($url);
        $query = $comps['query'];
        $output = array();
        parse_str($query, $output);

        $this->assertTrue(array_key_exists('code', $output) );
        $this->assertTrue(!empty($output['code']) );

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
            'scope'         => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_AccessType => OAuth2Protocol::OAuth2Protocol_AccessType_Offline,
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


        $this->assertResponseStatus(200);

        $content = $response->getContent();

        $response = json_decode($content);
        $access_token = $response->access_token;
        $refresh_token = $response->refresh_token;

        $this->assertTrue(!empty($access_token));
        $this->assertTrue(!empty($refresh_token));

    }


}