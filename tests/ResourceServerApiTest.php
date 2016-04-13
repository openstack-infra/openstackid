<?php
use Models\OAuth2\ResourceServer;
use Illuminate\Support\Facades\Config;

/**
 * Class ResourceServerApiTest
 * Test ResourceServer REST API
 */
class ResourceServerApiTest extends TestCase
{

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

    public function testGetById()
    {

        $resource_server = ResourceServer::where('host', '=', $this->current_host)->first();

        $response = $this->action("GET", "Api\ApiResourceServerController@get",
            $parameters = array('id' => $resource_server->id),
            array(),
            array(),
            array());

        $content = $response->getContent();
        $response_resource_server = json_decode($content);

        $this->assertResponseStatus(200);
        $this->assertTrue($response_resource_server->id === $resource_server->id);
    }

    public function testGetByPage()
    {

        $response = $this->action("GET", "Api\ApiResourceServerController@getByPage",
            $parameters = array('page_nbr' => 1, 'page_size' => 10),
            array(),
            array(),
            array());

        $content = $response->getContent();
        $list = json_decode($content);
        $this->assertTrue(isset($list->total_items) && intval($list->total_items) > 0);
        $this->assertResponseStatus(200);
    }

    public function testCreate()
    {

        $data = array(
            'host' => 'www.resource.server.2.test.com',
            'ips' => '10.0.0.1',
            'friendly_name' => 'Resource Server 2',
            'active' => true,
        );

        $response = $this->action("POST", "Api\ApiResourceServerController@create",
            $data,
            array(),
            array(),
            array());

        $content = $response->getContent();
        $json_response = json_decode($content);
        $this->assertTrue(isset($json_response->resource_server_id));
        $this->assertTrue(!empty($json_response->resource_server_id));
        $this->assertResponseStatus(201);
    }

    public function testRegenerateClientSecret()
    {

        $data = array(
            'host' => 'www.resource.server.3.test.com',
            'ips' => '10.0.0.2',
            'friendly_name' => 'Resource Server 3',
            'active' => true,
        );


        $response = $this->action("POST", "Api\ApiResourceServerController@create",
            $data,
            array(),
            array(),
            array());

        $content = $response->getContent();

        $json_response = json_decode($content);

        $new_id = $json_response->resource_server_id;

        $response = $this->action("GET", "Api\ApiResourceServerController@get", $parameters = array('id' => $new_id),
            array(),
            array(),
            array());

        $content = $response->getContent();

        $json_response = json_decode($content);


        $client_secret = $json_response->client_secret;

        $response = $this->action("PUT", "Api\ApiResourceServerController@regenerateClientSecret",
            $parameters = array('id' => $new_id),
            array(),
            array(),
            array());


        $content = $response->getContent();

        $json_response = json_decode($content);

        $new_secret = $json_response->new_secret;

        $this->assertTrue(!empty($new_secret));
        $this->assertTrue($new_secret !== $client_secret);

        $this->assertResponseStatus(200);

    }

    public function testDelete()
    {

        $data = array(
            'host' => 'www.resource.server.4.test.com',
            'ips' => '10.0.0.4',
            'friendly_name' => 'Resource Server 4',
            'active' => true,
        );


        $response = $this->action("POST", "Api\ApiResourceServerController@create",
            $parameters = $data,
            array(),
            array(),
            array());

        $content = $response->getContent();

        $json_response = json_decode($content);

        $new_id = $json_response->resource_server_id;

        $response = $this->action("DELETE", "Api\ApiResourceServerController@delete", $parameters = array('id' => $new_id),
            array(),
            array(),
            array());

        $this->assertResponseStatus(204);


        $response = $this->action("GET", "Api\ApiResourceServerController@get", $parameters = array('id' => $new_id),
            array(),
            array(),
            array());

        $content = $response->getContent();

        $json_response = json_decode($content);

        $this->assertResponseStatus(404);

        $this->assertTrue($json_response->error === 'resource server not found');
    }

    public function testDeleteExistingOne()
    {

        $resource_server = ResourceServer::where('host', '=', $this->current_host)->first();

        $new_id = $resource_server->id;

        $response = $this->action("DELETE", "Api\ApiResourceServerController@delete", $parameters = array('id' => $new_id),
            array(),
            array(),
            array());


        $this->assertResponseStatus(204);


        $response = $this->action("GET", "Api\ApiResourceServerController@get", $parameters = array('id' => $new_id),
            array(),
            array(),
            array());

        $this->assertResponseStatus(404);

    }

    public function testUpdate()
    {

        $data = array(
            'host' => 'www.resource.server.5.test.com',
            'ips' => '10.0.0.8',
            'friendly_name' => 'Resource Server 5',
            'active' => true,
        );

        $response = $this->action("POST", "Api\ApiResourceServerController@create", $parameters = $data,
            array(),
            array(),
            array());

        $content = $response->getContent();

        $json_response = json_decode($content);

        $new_id = $json_response->resource_server_id;

        $data_update = array(
            'id' => $new_id,
            'host' => 'www.resource.server.5.test.com',
            'ips' => '127.0.0.2',
            'friendly_name' => 'Resource Server 6',
        );

        $response = $this->action("PUT", "Api\ApiResourceServerController@update", $parameters = $data_update, array(),
            array(),
            array());

        $content = $response->getContent();

        $json_response = json_decode($content);

        $this->assertResponseStatus(200);

        $response = $this->action("GET", "Api\ApiResourceServerController@get", $parameters = array('id' => $new_id),
            array(),
            array(),
            array());

        $content = $response->getContent();

        $updated_values = json_decode($content);

        $this->assertTrue($updated_values->ips === '127.0.0.2');
        $this->assertTrue($updated_values->friendly_name === 'Resource Server 6');
        $this->assertResponseStatus(200);
    }

    public function testUpdateStatus()
    {

        $data = array(
            'host' => 'www.resource.server.7.test.com',
            'ips' => '127.0.0.8',
            'friendly_name' => 'Resource Server 7',
            'active' => true,
        );

        $response = $this->action("POST", "Api\ApiResourceServerController@create", $data);
        $this->assertResponseStatus(201);
        $content = $response->getContent();
        $json_response = json_decode($content);
        $new_id = $json_response->resource_server_id;
        $response = $this->action("DELETE", "Api\ApiResourceServerController@deactivate", array('id' => $new_id));
        $this->assertResponseStatus(200);
        $content = $response->getContent();
        $json_response = json_decode($content);
        $this->assertTrue($json_response === 'ok');
        $response = $this->action("GET", "Api\ApiResourceServerController@get", $parameters = array('id' => $new_id));
        $this->assertResponseStatus(200);
        $content = $response->getContent();
        $updated_values = json_decode($content);
        $this->assertTrue($updated_values->active === false);
    }

}