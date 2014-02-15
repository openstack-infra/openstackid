<?php

use oauth2\OAuth2Protocol;

/**
 * Class ApiTest
 */
class ApiTest extends TestCase {


    private $current_realm;

    protected function prepareForTests()
    {
        parent::prepareForTests();
        //Route::enableFilters();
        $this->current_realm = Config::get('app.url');
    }

    public function testGetById(){

        $api = Api::where('name','=','api')->first();

        $response = $this->action("GET", "ApiController@get",
            $parameters = array('id' => $api->id),
            array(),
            array(),
            array());

        $content                  = $response->getContent();
        $response_api = json_decode($content);

        $this->assertResponseStatus(200);
        $this->assertTrue($response_api->id === $api->id);
    }

    public function testGetByPage(){

        $response = $this->action("GET", "ApiController@getByPage",
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

        $resource_server = ResourceServer::where('host','=','dev.openstackid.com')->first();

        $data = array(
            'name'               => 'test-api',
            'description'        => 'test api',
            'active'             => true,
            'resource_server_id' => $resource_server->id,
        );

        $response = $this->action("POST", "ApiController@create",
            $data,
            array(),
            array(),
            array());

        $content = $response->getContent();
        $json_response = json_decode($content);

        $this->assertResponseStatus(201);
        $this->assertTrue(isset($json_response->api_id) && !empty($json_response->api_id));
    }

    public function testDelete(){

        $resource_server = ResourceServer::where('host','=','dev.openstackid.com')->first();

        $data = array(
            'name'               => 'test-api',
            'description'        => 'test api',
            'active'             => true,
            'resource_server_id' => $resource_server->id,
        );

        $response = $this->action("POST", "ApiController@create",
            $data,
            array(),
            array(),
            array());

        $content = $response->getContent();
        $json_response = json_decode($content);

        $this->assertResponseStatus(201);
        $this->assertTrue(isset($json_response->api_id) && !empty($json_response->api_id));

        $new_id = $json_response->api_id;
        $response = $this->action("DELETE", "ApiController@delete",$parameters = array('id' => $new_id),
            array(),
            array(),
            array());

        $this->assertResponseStatus(204);

        $response = $this->action("GET", "ApiController@get",
            $parameters = array('id' => $new_id),
            array(),
            array(),
            array());

        $content                  = $response->getContent();
        $response_api_endpoint    = json_decode($content);
        $this->assertResponseStatus(404);
    }

    public function testUpdate(){

        $resource_server = ResourceServer::where('host','=','dev.openstackid.com')->first();

        $data = array(
            'name'               => 'test-api',
            'description'        => "test api",
            'active'             => true,
            'resource_server_id' => $resource_server->id,
        );

        $response = $this->action("POST", "ApiController@create",
            $data,
            array(),
            array(),
            array());

        $content = $response->getContent();
        $json_response = json_decode($content);

        $this->assertResponseStatus(201);
        $this->assertTrue(isset($json_response->api_id) && !empty($json_response->api_id));

        $new_id = $json_response->api_id;
        //update it

        $data_update = array(
            'id'                => $new_id,
            'name'               => 'test-api-updated',
            'description'        => 'test api updated',
        );

        $response = $this->action("PUT", "ApiController@update",$parameters = $data_update, array(),
            array(),
            array());

        $content = $response->getContent();

        $json_response = json_decode($content);

        $this->assertResponseStatus(200);


        $response = $this->action("GET", "ApiController@get",
            $parameters = array('id' =>$new_id),
            array(),
            array(),
            array());

        $content = $response->getContent();

        $updated_values = json_decode($content);

        $this->assertTrue($updated_values->name === 'test-api-updated');
        $this->assertResponseStatus(200);
    }

    public function testUpdateStatus(){

        $resource_server = ResourceServer::where('host','=','dev.openstackid.com')->first();

        $data = array(
            'name'               => 'test-api',
            'description'        => 'test api',
            'active'             => true,
            'resource_server_id' => $resource_server->id,
        );

        $response = $this->action("POST", "ApiController@create",$data);
	    $this->assertResponseStatus(201);

        $content = $response->getContent();
        $json_response = json_decode($content);

        $this->assertTrue(isset($json_response->api_id) && !empty($json_response->api_id));

        $new_id = $json_response->api_id;
        //update status

        $response = $this->action("PUT", "ApiController@activate",array('id'     => $new_id));
	    $this->assertResponseStatus(200);

        $content = $response->getContent();

        $json_response = json_decode($content);

        $this->assertTrue($json_response==='ok');


        $response = $this->action("GET", "ApiController@get",$parameters = array('id' => $new_id));

	    $this->assertResponseStatus(200);
        $content = $response->getContent();
	    $updated_values = json_decode($content);
        $this->assertTrue($updated_values->active == true);
    }

    public function testDeleteExisting(){

        $resource_server_api        = Api::where('name','=','resource-server')->first();

        $id = $resource_server_api->id;

        $response = $this->action("DELETE", "ApiController@delete",$parameters = array('id' => $id),
            array(),
            array(),
            array());


        $this->assertResponseStatus(204);

        $response = $this->action("GET", "ApiController@get",
            $parameters = array('id' => $id),
            array(),
            array(),
            array());

        $this->assertResponseStatus(404);
    }
}