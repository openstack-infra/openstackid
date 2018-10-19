<?php
/**
 * Copyright 2016 OpenStack Foundation
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
use Models\OAuth2\ApiScope;
use Models\OAuth2\Api;
use Tests\BrowserKitTestCase;
/**
 * Class ApiScopeTest
 */
final class ApiScopeTest extends BrowserKitTestCase {

    private $current_realm;

    protected function prepareForTests()
    {
        parent::prepareForTests();
        $this->withoutMiddleware();
        $this->current_realm = Config::get('app.url');
    }

    /**
     * testGetById
     * @covers get scope api by id
     */
    public function testGetById(){

        $scope = ApiScope::where('name','=', sprintf('%s/api-scope/read',$this->current_realm))->first();
        $this->assertTrue(!is_null($scope));

        $response = $this->action("GET", "Api\ApiScopeController@get",
            $parameters = array('id' => $scope->id),
            array(),
            array(),
            array());

        $content      = $response->getContent();
        $response_scope = json_decode($content);

        $this->assertResponseStatus(200);
        $this->assertTrue($response_scope->id === $scope->id);
    }

    /**
     * testGetByPage
     * @covers get api scopes by list (paginated)
     */
    public function testGetByPage(){
        $response = $this->action("GET", "Api\ApiScopeController@getByPage",
            $parameters = array('offset' => 1,'limit'=>10),
            array(),
            array(),
            array());

        $content         = $response->getContent();
        $list            = json_decode($content);
        $this->assertTrue(isset($list->total_items) && intval($list->total_items)>0);
        $this->assertResponseStatus(200);
    }

    /**
     * testCreate
     * @covers create a new api scope
     */
    public function testCreate(){

        $api = Api::where('name','=','api-endpoint')->first();

        $this->assertTrue(!is_null($api));

        $data = array(
            'name'               => 'https://test-scope/read.only',
            'description'        => 'test scope.',
            'short_description'  => 'test scope.',
            'active'             => true,
            'system'             => true,
            'default'            => true,
            'api_id'             => $api->id,
            'assigned_by_groups' => false,
        );

        $response = $this->action("POST", "Api\ApiScopeController@create",
            $data,
            array(),
            array(),
            array());

        $content = $response->getContent();
        $json_response = json_decode($content);

        $this->assertResponseStatus(201);
        $this->assertTrue(isset($json_response->scope_id) && !empty($json_response->scope_id));
    }

    /**
     * testDeleteExisting
     * @covers deletes an existing api scope
     */
    public function testDeleteExisting(){

        $scope = ApiScope::where('name','=', sprintf('%s/api-scope/read',$this->current_realm))->first();

        $this->assertTrue(!is_null($scope));

        $id = $scope->id;

        $response = $this->action("DELETE", "Api\ApiScopeController@delete",$parameters = array('id' => $id),
            array(),
            array(),
            array());

        $this->assertResponseStatus(204);

    }

} 