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
use Models\OAuth2\ApiEndpoint;
use Models\OAuth2\Api;
use Models\OAuth2\ApiScope;
use Tests\BrowserKitTestCase;
/**
 * Class ApiEndpointTest
 */
final class ApiEndpointTest extends BrowserKitTestCase {

    private $current_realm;

    protected function prepareForTests()
    {
        parent::prepareForTests();
        $this->withoutMiddleware();
        $this->current_realm = Config::get('app.url');
    }

    /**
     * testGetById
     * @covers get api endpoint by id
     */
    public function testGetById(){

        $api_endpoint = ApiEndpoint::where('name','=','get-api')->first();
        $this->assertTrue(!is_null($api_endpoint));

        $response = $this->action("GET", "Api\ApiEndpointController@get",
            $parameters = array('id' =>$api_endpoint->id),
            array(),
            array(),
            array());

        $content      = $response->getContent();
        $response_api = json_decode($content);

        $this->assertResponseStatus(200);
        $this->assertTrue($response_api->id === $api_endpoint->id);
    }

    /**
     * testGetByPage
     * @covers get api endpoint by list (paginated)
     */
    public function testGetByPage(){
        $response = $this->action("GET", "Api\ApiEndpointController@getByPage",
            $parameters = array('offset' => 1,'limit'=>10),
            array(),
            array(),
            array());

        $content         = $response->getContent();
        $list            = json_decode($content);
        $this->assertTrue(isset($list->total_items) && intval($list->total_items)>0);
        $this->assertResponseStatus(200);
    }

    public function testCreate(){

        $api = Api::where('name','=','api-endpoint')->first();
        $this->assertTrue(!is_null($api));

        $data = array(
            'name'               => 'test-api-endpoint',
            'description'        => 'test api endpoint, allows test api endpoints.',
            'active'             => true,
            'route'              => '/api/v1/api-endpoint/test',
            'http_method'        => 'POST',
            'api_id'             => $api->id,
            'allow_cors'        => true,
            'rate_limit'        => 60,
        );

        $response = $this->action("POST", "Api\ApiEndpointController@create",
            $data,
            array(),
            array(),
            array());

        $content = $response->getContent();
        $json_response = json_decode($content);

        $this->assertResponseStatus(201);
        $this->assertTrue(isset($json_response->api_endpoint_id) && !empty($json_response->api_endpoint_id));
    }

    public function testUpdate(){

        $api = Api::where('name','=','api-endpoint')->first();
        $this->assertTrue(!is_null($api));

        $data = array(
            'name'               => 'test-api-endpoint',
            'description'        => 'test api endpoint, allows test api endpoints.',
            'active'             => true,
            'route'              => '/api/v1/api-endpoint/test',
            'http_method'        => 'POST',
            'api_id'             => $api->id,
            'allow_cors'        => true,
            'rate_limit'        => 60,
        );

        $response = $this->action("POST", "Api\ApiEndpointController@create",
            $data,
            array(),
            array(),
            array());

        $content = $response->getContent();
        $json_response = json_decode($content);

        $this->assertResponseStatus(201);
        $this->assertTrue(isset($json_response->api_endpoint_id) && !empty($json_response->api_endpoint_id));

        //update recently created

        $data_updated = array(
            'id'                 => $json_response->api_endpoint_id,
            'name'               => 'test-api-endpoint-update',
        );

        $response = $this->action("PUT", "Api\ApiEndpointController@update",$parameters = $data_updated, array(),
            array(),
            array());

        $content = $response->getContent();

        $json_response = json_decode($content);
        $this->assertTrue($json_response ==="ok");
        $this->assertResponseStatus(200);

    }

    public function testUpdateStatus(){

        $api = Api::where('name','=','api-endpoint')->first();
        $this->assertTrue(!is_null($api));
        $data = array(
            'name'               => 'test-api-endpoint',
            'description'        => 'test api endpoint, allows test api endpoints.',
            'active'             => true,
            'route'              => '/api/v1/api-endpoint/test',
            'http_method'        => 'POST',
            'api_id'             => $api->id,
            'allow_cors'        => true,
            'rate_limit'        => 60,
        );

        $response = $this->action("POST", "Api\ApiEndpointController@create", $data);
        $this->assertResponseStatus(201);
        $content = $response->getContent();
        $json_response = json_decode($content);
        $this->assertTrue(isset($json_response->api_endpoint_id) && !empty($json_response->api_endpoint_id));
        $new_id = $json_response->api_endpoint_id;
        //update status

        $response = $this->action('DELETE',"Api\ApiEndpointController@deactivate", array('id' => $new_id) );
        $this->assertResponseStatus(200);
        $content = $response->getContent();
        $json_response = json_decode($content);
        $this->assertTrue($json_response==='ok');

        $response = $this->action("GET", "Api\ApiEndpointController@get",array('id' => $new_id));
        $this->assertResponseStatus(200);
        $content = $response->getContent();
        $updated_values = json_decode($content);
        $this->assertTrue($updated_values->active == false);
    }

    public function testDeleteExisting(){

        $api_endpoint        = ApiEndpoint::where('name','=','update-api-endpoint-status')->first();

        $this->assertTrue(!is_null($api_endpoint));

        $id = $api_endpoint->id;

        $response = $this->action("DELETE", "Api\ApiEndpointController@delete",$parameters = array('id' => $id),
            array(),
            array(),
            array());

        $this->assertResponseStatus(204);

        $response = $this->action("GET", "Api\ApiEndpointController@get",
            $parameters = array('id' => $id),
            array(),
            array(),
            array());

        $content      = $response->getContent();

        $this->assertResponseStatus(404);
    }

    public function testAddRequiredScope(){

        $api_endpoint = ApiEndpoint::where('name','=','update-api-endpoint-status')->first();
        $this->assertTrue(!is_null($api_endpoint));
        $scope        = ApiScope::where('name','=',sprintf('%s/api-endpoint/read',$this->current_realm))->first();
        $this->assertTrue(!is_null($scope));

        $response = $this->action("PUT", "Api\ApiEndpointController@addRequiredScope",array(
            'id'       => $api_endpoint->id,
            'scope_id' => $scope->id), array(),
            array(),
            array());

        $this->assertResponseStatus(200);
        $content = $response->getContent();
        $this->assertTrue(json_decode($content)==='ok');

        $response = $this->action("GET", "Api\ApiEndpointController@get",
            $parameters = array('id' =>$api_endpoint->id),
            array(),
            array(),
            array());

        $content      = $response->getContent();
        $response_api_endpoint = json_decode($content);
        $this->assertTrue(is_array($response_api_endpoint->scopes) && count($response_api_endpoint->scopes)>2);
        $this->assertResponseStatus(200);
    }

    public function testRemoveRequiredScope(){

        $api_endpoint = ApiEndpoint::where('name','=','update-api-endpoint-status')->first();
        $this->assertTrue(!is_null($api_endpoint));
        $scope        = ApiScope::where('name','=',sprintf('%s/api-endpoint/update',$this->current_realm))->first();
        $this->assertTrue(!is_null($scope));

        $response = $this->action("DELETE", "Api\ApiEndpointController@removeRequiredScope",array(
            'id'       => $api_endpoint->id,
            'scope_id' => $scope->id), array(),
            array(),
            array());

        $this->assertResponseStatus(200);
        $content = $response->getContent();
        $response = json_decode($content);
        $this->assertTrue($response==='ok');

        $response = $this->action("GET", "Api\ApiEndpointController@get",
            $parameters = array('id' =>$api_endpoint->id),
            array(),
            array(),
            array());

        $content      = $response->getContent();
        $response_api_endpoint = json_decode($content);
        $this->assertTrue(is_array($response_api_endpoint->scopes) && count($response_api_endpoint->scopes)==1);
        $this->assertResponseStatus(200);
    }

} 