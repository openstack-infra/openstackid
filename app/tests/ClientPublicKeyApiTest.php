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
use jwk\JSONWebKeyTypes;
use jwk\JSONWebKeyPublicKeyUseValues;

class ClientPublicKeyApiTest extends TestCase {

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

    public function testCreate(){

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $client    =  Client::where('client_id','=', $client_id)->first();

        $data = array(
            'kid'         => 'test key',
            'pem_content' => TestKeys::$public_key2_pem,
            'usage'       => JSONWebKeyPublicKeyUseValues::Signature,
            'type'        => JSONWebKeyTypes::RSA
        );

        $response = $this->action("POST", "ClientPublicKeyApiController@create",
            $wildcards = array('id' => $client->id),
            $data,
            array(),
            array());

        $content = $response->getContent();
        $json_response = json_decode($content);

        $this->assertResponseStatus(201);
        $this->assertTrue(isset($json_response->id) && !empty($json_response->id));

        $public_key = $client->getPublicKeyByIdentifier('test key');

        $this->assertTrue(!is_null($public_key) && $json_response->id === $public_key->getId());
    }
}