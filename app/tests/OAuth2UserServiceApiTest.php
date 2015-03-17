<?php

use oauth2\resource_server\IUserService;

/**
 * Class OAuth2UserServiceApiTest
 */
class OAuth2UserServiceApiTest extends OAuth2ProtectedApiTest {


    /**
     * @covers OAuth2UserApiController::get()
     */
    public function testGetInfo(){
        $response = $this->action("GET", "OAuth2UserApiController@me",
            array(),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " .$this->access_token));

        $this->assertResponseStatus(200);
        $content   = $response->getContent();
        $user_info = json_decode($content);
    }

    public function testGetInfoCORS(){
        $response = $this->action("OPTION", "OAuth2UserApiController@me",
            array(),
            array(),
            array(),
            array(
                "HTTP_Authorization" => " Bearer " .$this->access_token,
                'HTTP_Origin' => array('www.test.com','www.test1.com'),
                'HTTP_Access-Control-Request-Method'=>'GET',
            ));

        $this->assertResponseStatus(403);
        $content   = $response->getContent();
        $user_info = json_decode($content);
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