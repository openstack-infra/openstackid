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
use OAuth2\ResourceServer\IUserService;
/**
 * Class OAuth2UserServiceApiTest
 */
final class OAuth2UserServiceApiTest extends OAuth2ProtectedApiTest {


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