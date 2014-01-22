<?php
use oauth2\OAuth2Protocol;

/**
 * Class ApiEndpointTest
 * Api endpoint test suite
 */
class ApiEndpointTest extends TestCase {

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
        $api_endpoint = ApiEndpoint::where('name','=','get api')->first();
        $this->assertTrue(!is_null($api_endpoint));
        $response = $this->action("GET", "ApiEndpointController@get",
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
        $response = $this->action("GET", "ApiEndpointController@getByPage",
            $parameters = array('page_nbr' => 1,'page_size'=>10),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content         = $response->getContent();
        $list            = json_decode($content);
        $this->assertTrue(isset($list->total_items) && intval($list->total_items)>0);
        $this->assertResponseStatus(200);
    }

} 