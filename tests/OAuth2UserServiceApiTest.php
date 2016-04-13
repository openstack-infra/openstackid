<?php

use OAuth2\ResourceServer\IUserService;

/**
 * Class OAuth2UserServiceApiTest
 */
class OAuth2UserServiceApiTest extends OAuth2ProtectedApiTest {


    /**
     * @covers OAuth2UserApiController::get()
     */
    public function testGetInfo(){

        $response = $this->action("GET", "Api\OAuth2\OAuth2UserApiController@me",
            array(),
            array(),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $this->assertResponseStatus(200);
        $content   = $response->getContent();
        $user_info = json_decode($content);
    }

    public function testGetInfoCORS(){
        $response = $this->action("GET", "Api\OAuth2\OAuth2UserApiController@me",
            array(),
            array(),
            array(),
            array(),
            array(
                "HTTP_Authorization"                  => " Bearer " .$this->access_token,
                'HTTP_Origin'                         => array('www.test.com'),
                'HTTP_Host'                           => 'local.openstackid.openstack.org',
                'HTTP_Access-Control-Request-Method'  => 'GET',
            ));

        $this->assertResponseStatus(200);
        $content   = $response->getContent();
    }

    protected function getScopes()
    {
        $scope = array(
            IUserService::UserProfileScope_Address,
            IUserService::UserProfileScope_Email,
            IUserService::UserProfileScope_Profile
        );

        return $scope;
    }
}