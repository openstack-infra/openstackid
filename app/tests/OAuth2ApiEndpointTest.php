<?php

use oauth2\OAuth2Protocol;

/**
 * Class OAuth2ApiEndpointTest
 * Test Suite for OAuth2 Protected Api Endpoints
 */
class OAuth2ApiEndpointTest extends TestCase {

    private $access_token;
    private $client_id;
    private $client_secret;
    private $current_realm;

    protected function prepareForTests()
    {
        parent::prepareForTests();
        Route::enableFilters();
        $this->current_realm = Config::get('app.url');
        $this->client_id     = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $this->client_secret = 'ITc/6Y5N7kOtGKhg';

        $scope = array(
            sprintf('%s/api-endpoint/read',$this->current_realm),
            sprintf('%s/api-endpoint/write',$this->current_realm),
            sprintf('%s/api-endpoint/delete',$this->current_realm),
            sprintf('%s/api-endpoint/update',$this->current_realm),
            sprintf('%s/api-endpoint/update.status',$this->current_realm),
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

    /**
     * testGetById
     * @covers get api endpoint by id
     */
    public function testGetById(){

        $api_endpoint = ApiEndpoint::where('name','=','get-api')->first();
        $this->assertTrue(!is_null($api_endpoint));

        $response = $this->action("GET", "OAuth2ProtectedApiEndpointController@get",
            $parameters = array('id' =>$api_endpoint->id),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

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
        $response = $this->action("GET", "OAuth2ProtectedApiEndpointController@getByPage",
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

        $api = Api::where('name','=','api-endpoint')->first();
        $this->assertTrue(!is_null($api));

        $data = array(
            'name'               => 'test-api-endpoint',
            'description'        => 'test api endpoint, allows test api endpoints.',
            'active'             => true,
            'route'              => '/api/v1/api-endpoint/test',
            'http_method'        => 'POST',
            'api_id'             => $api->id
        );

        $response = $this->action("POST", "OAuth2ProtectedApiEndpointController@create",
            $data,
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content = $response->getContent();
        $json_response = json_decode($content);

        $this->assertResponseStatus(200);
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
            'api_id'             => $api->id
        );

        $response = $this->action("POST", "OAuth2ProtectedApiEndpointController@create",
            $data,
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content = $response->getContent();
        $json_response = json_decode($content);

        $this->assertResponseStatus(200);
        $this->assertTrue(isset($json_response->api_endpoint_id) && !empty($json_response->api_endpoint_id));

        //update recently created

        $data_updated = array(
            'id'                 => $json_response->api_endpoint_id,
            'name'               => 'test-api-endpoint-update',
        );

        $response = $this->action("PUT", "OAuth2ProtectedApiEndpointController@update",$parameters = $data_updated, array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

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
            'api_id'             => $api->id
        );

        $response = $this->action("POST", "OAuth2ProtectedApiEndpointController@create",
            $data,
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content = $response->getContent();
        $json_response = json_decode($content);

        $this->assertResponseStatus(200);
        $this->assertTrue(isset($json_response->api_endpoint_id) && !empty($json_response->api_endpoint_id));

        $new_id = $json_response->api_endpoint_id;
        //update status

        $response = $this->action("GET", "OAuth2ProtectedApiEndpointController@updateStatus",array(
                'id'     => $new_id,
                'active' => 'false'), array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content = $response->getContent();

        $json_response = json_decode($content);

        $this->assertTrue($json_response==='ok');
        $this->assertResponseStatus(200);

        $response = $this->action("GET", "OAuth2ProtectedApiEndpointController@get",$parameters = array('id' => $new_id), array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content = $response->getContent();

        $updated_values = json_decode($content);
        $this->assertTrue($updated_values->active === 0);
        $this->assertResponseStatus(200);
    }

    public function testDeleteExisting(){

        $api_endpoint        = ApiEndpoint::where('name','=','update-api-endpoint-status')->first();

        $this->assertTrue(!is_null($api_endpoint));

        $id = $api_endpoint->id;

        $response = $this->action("DELETE", "OAuth2ProtectedApiEndpointController@delete",$parameters = array('id' => $id),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content = $response->getContent();

        $json_response = json_decode($content);

        $this->assertTrue($json_response==='ok');

        $this->assertResponseStatus(200);

        $response = $this->action("GET", "OAuth2ProtectedApiEndpointController@get",
            $parameters = array('id' => $id),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content                  = $response->getContent();
        $response_api_endpoint    = json_decode($content);
        $this->assertTrue(isset($response_api_endpoint->error));
        $this->assertTrue($response_api_endpoint->error==='api endpoint not found');
        $this->assertResponseStatus(404);
    }

    public function testAddRequiredScope(){

        $api_endpoint = ApiEndpoint::where('name','=','update-api-endpoint-status')->first();
        $this->assertTrue(!is_null($api_endpoint));
        $scope        = ApiScope::where('name','=',sprintf('%s/api-endpoint/read',$this->current_realm))->first();
        $this->assertTrue(!is_null($scope));

        $response = $this->action("GET", "OAuth2ProtectedApiEndpointController@addRequiredScope",array(
                'id'       => $api_endpoint->id,
                'scope_id' => $scope->id), array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $this->assertResponseStatus(200);
        $content = $response->getContent();
        $this->assertTrue(json_decode($content)==='ok');

        $response = $this->action("GET", "OAuth2ProtectedApiEndpointController@get",
            $parameters = array('id' =>$api_endpoint->id),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

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

        $response = $this->action("GET", "OAuth2ProtectedApiEndpointController@removeRequiredScope",array(
                'id'       => $api_endpoint->id,
                'scope_id' => $scope->id), array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $this->assertResponseStatus(200);
        $content = $response->getContent();
        $response = json_decode($content);
        $this->assertTrue($response==='ok');

        $response = $this->action("GET", "OAuth2ProtectedApiEndpointController@get",
            $parameters = array('id' =>$api_endpoint->id),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content      = $response->getContent();
        $response_api_endpoint = json_decode($content);
        $this->assertTrue(is_array($response_api_endpoint->scopes) && count($response_api_endpoint->scopes)==1);
        $this->assertResponseStatus(200);
    }

    public function testRemoveRequiredScopeMustFail(){

        $api_endpoint = ApiEndpoint::where('name','=','update-api-endpoint-status')->first();
        $this->assertTrue(!is_null($api_endpoint));
        $scope        = ApiScope::where('name','=',sprintf('%s/api-endpoint/read',$this->current_realm))->first();
        $this->assertTrue(!is_null($scope));

        $response = $this->action("GET", "OAuth2ProtectedApiEndpointController@removeRequiredScope",array(
                'id'       => $api_endpoint->id,
                'scope_id' => $scope->id), array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $this->assertResponseStatus(400);
        $content = $response->getContent();
        $response = json_decode($content);
        $this->assertTrue(isset($response->error));
    }

} 