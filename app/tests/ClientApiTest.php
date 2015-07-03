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
use oauth2\OAuth2Protocol;
use oauth2\models\IClient;
use auth\User;

class ClientApiTest extends TestCase {

    private $current_realm;

    private $current_host;

    protected function prepareForTests()
    {
        parent::prepareForTests();
        //Route::enableFilters();
        $this->current_realm = Config::get('app.url');
        $parts = parse_url($this->current_realm);
        $this->current_host = $parts['host'];
    }

    public function testGetById(){

        $client = Client::where('app_name','=','oauth2_test_app')->first();

        $response = $this->action("GET", "ClientApiController@get",
            $parameters = array('id' => $client->id),
            array(),
            array(),
            array());

        $content                  = $response->getContent();
        $response_client = json_decode($content);

        $this->assertResponseStatus(200);
        $this->assertTrue($response_client->id === $client->id);
    }


    public function testCreate(){

        $user =  User::where('identifier','=','sebastian.marcet')->first();

        $data = array(
            'user_id'            => $user->id,
            'app_name'           => 'test_app',
            'app_description'    => 'test app',
            'website'            => 'http://www.test.com',
            'application_type' => IClient::ApplicationType_Native
        );

        $response = $this->action("POST", "ClientApiController@create",
            $data,
            array(),
            array(),
            array());

        $content = $response->getContent();
        $json_response = json_decode($content);

        $this->assertResponseStatus(201);
        $this->assertTrue(isset($json_response->client_id) && !empty($json_response->client_id));
    }


}