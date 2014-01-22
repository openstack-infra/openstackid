<?php

use oauth2\OAuth2Protocol;

/**
 * Class ApiTest
 * Test Suite for OAuth2 Protected Api
 */
class ApiTest extends TestCase {

    private $access_token;
    private $client_id;
    private $client_secret;
    private $current_realm;

    protected function prepareForTests()
    {
        parent::prepareForTests();
        Route::enableFilters();
        $this->current_realm = Config::get('app.url');
        $this->client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $this->client_secret = 'ITc/6Y5N7kOtGKhg';

        $scope = array(
            sprintf('%s/api/read',$this->current_realm),
            sprintf('%s/api/write',$this->current_realm),
            sprintf('%s/api/delete',$this->current_realm),
            sprintf('%s/api/update',$this->current_realm),
            sprintf('%s/api/update.status',$this->current_realm),
        );

        //do get auth token...
        $params = array(
            OAuth2Protocol::OAuth2Protocol_GrantType => OAuth2Protocol::OAuth2Protocol_GrantType_ClientCredentials,
            OAuth2Protocol::OAuth2Protocol_Scope => implode(' ',$scope)
        );

        //get access token for api ...

        $response = $this->action("POST", "OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            // Symfony interally prefixes headers with "HTTP", so
            array("HTTP_Authorization" => " Basic " . base64_encode($this->client_id . ':' . $this->client_secret)));

        $this->assertResponseStatus(200);

        $content  = $response->getContent();

        $response = json_decode($content);

        $this->access_token = $response->access_token;
    }

    public function testGetById(){

        $api = Api::where('name','=','api')->first();

        $response = $this->action("GET", "ApiController@get",
            $parameters = array('id' => $api->id),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content                  = $response->getContent();
        $response_api = json_decode($content);

        $this->assertResponseStatus(200);
        $this->assertTrue($response_api->id === $api->id);
    }

    public function testGetByPage(){

        $response = $this->action("GET", "ApiController@getByPage",
            $parameters = array('page_nbr' => 1,'page_size'=>10),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

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
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content = $response->getContent();
        $json_response = json_decode($content);

        $this->assertResponseStatus(200);
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
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content = $response->getContent();
        $json_response = json_decode($content);

        $this->assertResponseStatus(200);
        $this->assertTrue(isset($json_response->api_id) && !empty($json_response->api_id));

        $new_id = $json_response->api_id;
        $response = $this->action("DELETE", "ApiController@delete",$parameters = array('id' => $new_id),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content = $response->getContent();

        $json_response = json_decode($content);

        $this->assertTrue($json_response==='ok');

        $this->assertResponseStatus(200);

        $response = $this->action("GET", "ApiController@get",
            $parameters = array('id' => $new_id),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

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
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content = $response->getContent();
        $json_response = json_decode($content);

        $this->assertResponseStatus(200);
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
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content = $response->getContent();

        $json_response = json_decode($content);

        $this->assertResponseStatus(200);


        $response = $this->action("GET", "ApiController@get",
            $parameters = array('id' =>$new_id),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

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

        $response = $this->action("POST", "ApiController@create",
            $data,
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content = $response->getContent();
        $json_response = json_decode($content);

        $this->assertResponseStatus(200);
        $this->assertTrue(isset($json_response->api_id) && !empty($json_response->api_id));

        $new_id = $json_response->api_id;
        //update status

        $response = $this->action("GET", "ApiController@updateStatus",array(
                'id'     => $new_id,
                'active' => 'false'), array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content = $response->getContent();

        $json_response = json_decode($content);

        $this->assertTrue($json_response==='ok');
        $this->assertResponseStatus(200);

        $response = $this->action("GET", "ApiController@get",$parameters = array('id' => $new_id), array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content = $response->getContent();

        $updated_values = json_decode($content);
        $this->assertTrue($updated_values->active === 0);
        $this->assertResponseStatus(200);
    }

    public function testDeleteExisting(){

        $resource_server_api        = Api::where('name','=','resource-server')->first();

        $id = $resource_server_api->id;

        $response = $this->action("DELETE", "ApiController@delete",$parameters = array('id' => $id),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content = $response->getContent();

        $json_response = json_decode($content);

        $this->assertTrue($json_response==='ok');

        $this->assertResponseStatus(200);

        $response = $this->action("GET", "ApiController@get",
            $parameters = array('id' => $id),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content                  = $response->getContent();
        $response_api_endpoint    = json_decode($content);
        $this->assertResponseStatus(404);
    }
} 