<?php
/**
 * Copyright 2014 Openstack Foundation
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

use oauth2\OAuth2Protocol;
use auth\User;
use utils\services\IAuthService;

/**
 * Class OAuth2ProtectedApiTest
 */
abstract class OAuth2ProtectedApiTest extends OpenStackIDBaseTest {

    protected $access_token;
    protected $client_id;
    protected $client_secret;
    protected $current_realm;

    abstract protected function getScopes();

    protected function prepareForTests()
    {
        parent::prepareForTests();
        Route::enableFilters();

        $this->current_realm = Config::get('app.url');

        $user = User::where('external_id', '=', 'smarcet@gmail.com')->first();

        $this->be($user);

        Session::start();

        $scope = $this->getScopes();

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

        $status  = $response->getStatusCode();
        $url     = $response->getTargetUrl();
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
}