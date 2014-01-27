<?php
use oauth2\OAuth2Protocol;
/**
 * Class OAuth2ApiScopeTest
 * Scope Api test suite
 */
class OAuth2ApiScopeTest  extends TestCase {

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
            sprintf('%s/api-scope/read',$this->current_realm),
            sprintf('%s/api-scope/write',$this->current_realm),
            sprintf('%s/api-scope/delete',$this->current_realm),
            sprintf('%s/api-scope/update',$this->current_realm),
            sprintf('%s/api-scope/update.status',$this->current_realm),
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
     * @covers get scope api by id
     */
    public function testGetById(){

        $scope = ApiScope::where('name','=', sprintf('%s/api-scope/read',$this->current_realm))->first();
        $this->assertTrue(!is_null($scope));

        $response = $this->action("GET", "OAuth2ProtectedApiScopeController@get",
            $parameters = array('id' => $scope->id),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

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
        $response = $this->action("GET", "OAuth2ProtectedApiScopeController@getByPage",
            $parameters = array('page_nbr' => 1,'page_size'=>10),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

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
            'api_id'             => $api->id
        );

        $response = $this->action("POST", "OAuth2ProtectedApiScopeController@create",
            $data,
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content = $response->getContent();
        $json_response = json_decode($content);

        $this->assertResponseStatus(200);
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

        $response = $this->action("DELETE", "OAuth2ProtectedApiScopeController@delete",$parameters = array('id' => $id),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $content = $response->getContent();

        $json_response = json_decode($content);

        $this->assertTrue($json_response==='ok');

        $this->assertResponseStatus(200);

    }

    /**
     * testUpdate
     * @covers updates an existing scope
     */
    public function testUpdate(){

    }

    /**
     * testUpdateStatus
     * @covers updates status of an existing scope
     */
    public function testUpdateStatus(){

    }

} 