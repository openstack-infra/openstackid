<?php

class ResourceServerApiTest extends TestCase {


    public function testGetById(){

        $resource_server = ResourceServer::where('host','=','https://www.resource.test1.com')->first();

        $response = $this->action("GET", "ApiResourceServerController@get",
            $parameters = array('id' => $resource_server->id),
            $files      = array(),
            $server     = array(),
            $content    = array());

        $content         = $response->getContent();
        $response_resource_server = json_decode($content);

        $this->assertResponseStatus(200);
        $this->assertTrue($response_resource_server->id === $resource_server->id);
    }

    public function testGetByPage(){

        $response = $this->action("GET", "ApiResourceServerController@getByPage",
            $parameters = array('page_nbr' => 1,'page_size'=>10),
            $files      = array(),
            $server     = array(),
            $content    = array());

        $content         = $response->getContent();
        $list = json_decode($content);

        $this->assertResponseStatus(200);
    }

    public function testCreate(){

        $data = array(
            'host' => 'www.resource.server.2.test.com',
            'ip' => '127.0.0.1',
            'friendly_name' => 'Resource Server 2',
            'active' => 'true',
        );


        $response = $this->action("POST", "ApiResourceServerController@create",
            $wildcards  = array(),
            $parameters = $data,
            $files      = array(),
            $server     = array(),
            $content    = null);

        $content = $response->getContent();
        $json_response = json_decode($content);
        $this->assertResponseStatus(200);
    }

    public function testRegenerateClientSecret(){

        $data = array(
            'host' => 'www.resource.server.3.test.com',
            'ip' => '127.0.0.1',
            'friendly_name' => 'Resource Server 3',
            'active' => true,
        );


        $response = $this->action("POST", "ApiResourceServerController@create",
            $wildcards  = array(),
            $parameters = $data,
            $files      = array(),
            $server     = array(),
            $content    = null);

        $content = $response->getContent();

        $json_response = json_decode($content);

        $new_id = $json_response->resource_server_id;

        $response = $this->action("GET", "ApiResourceServerController@get",$parameters = array('id' => $new_id));

        $content = $response->getContent();

        $json_response = json_decode($content);


        $client_secret = $json_response->client_secret;

        $response = $this->action("GET", "ApiResourceServerController@regenerateClientSecret",$parameters = array('id'=>$new_id));


        $content = $response->getContent();

        $json_response = json_decode($content);

        $new_secret = $json_response->new_secret;

        $this->assertTrue(!empty($new_secret));
        $this->assertTrue($new_secret!==$client_secret);

        $this->assertResponseStatus(200);

    }

    public function testDelete(){

        $data = array(
            'host' => 'www.resource.server.4.test.com',
            'ip' => '127.0.0.1',
            'friendly_name' => 'Resource Server 4',
            'active' => true,
        );


        $response = $this->action("POST", "ApiResourceServerController@create",
            $wildcards  = array(),
            $parameters = $data,
            $files      = array(),
            $server     = array(),
            $content    = null);

        $content = $response->getContent();

        $json_response = json_decode($content);

        $new_id = $json_response->resource_server_id;

        $response = $this->action("DELETE", "ApiResourceServerController@delete",$parameters = array('id' => $new_id));

        $content = $response->getContent();

        $json_response = json_decode($content);

        $this->assertTrue($json_response==='ok');

        $this->assertResponseStatus(200);


        $response = $this->action("GET", "ApiResourceServerController@get",$parameters = array('id' => $new_id));

        $content = $response->getContent();

        $json_response = json_decode($content);

        $this->assertResponseStatus(404);

        $this->assertTrue($json_response->error==='resource server not found');
    }

    public function testUpdate(){

        $data = array(
            'host' => 'www.resource.server.5.test.com',
            'ip' => '127.0.0.1',
            'friendly_name' => 'Resource Server 5',
            'active' => true,
        );

        $response = $this->action("POST", "ApiResourceServerController@create",$parameters = $data);

        $content = $response->getContent();

        $json_response = json_decode($content);

        $new_id = $json_response->resource_server_id;

        $data_update = array(
            'id'            => $new_id,
            'host'          => 'www.resource.server.5.test.com',
            'ip'            => '127.0.0.2',
            'friendly_name' => 'Resource Server 6',
        );

        $response = $this->action("PUT", "ApiResourceServerController@update",$parameters = $data_update);

        $content = $response->getContent();

        $json_response = json_decode($content);

        $this->assertResponseStatus(200);

        $response = $this->action("GET", "ApiResourceServerController@get",$parameters = array('id' => $new_id));

        $content = $response->getContent();

        $updated_values = json_decode($content);

        $this->assertTrue($updated_values->ip === '127.0.0.2');
        $this->assertTrue($updated_values->friendly_name === 'Resource Server 6');
        $this->assertResponseStatus(200);
    }

    public function testUpdateStatus(){

        $data = array(
            'host' => 'www.resource.server.7.test.com',
            'ip' => '127.0.0.1',
            'friendly_name' => 'Resource Server 7',
            'active' => true,
        );

        $response = $this->action("POST", "ApiResourceServerController@create",$parameters = $data);

        $content = $response->getContent();

        $json_response = json_decode($content);

        $new_id = $json_response->resource_server_id;


        $response = $this->action("GET", "ApiResourceServerController@updateStatus",array(
            'id'     => $new_id,
            'active' => 'false'));

        $content = $response->getContent();

        $json_response = json_decode($content);

        $this->assertTrue($json_response==='ok');
        $this->assertResponseStatus(200);

        $response = $this->action("GET", "ApiResourceServerController@get",$parameters = array('id' => $new_id));

        $content = $response->getContent();

        $updated_values = json_decode($content);
        $this->assertTrue($updated_values->active === 0);
        $this->assertResponseStatus(200);

    }

}
