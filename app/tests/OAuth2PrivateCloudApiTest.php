<?php
/**
 * Copyright 2015 Openstack Foundation
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

use models\marketplace\repositories\ICompanyServiceRepository;

/**
 * Class OAuth2PrivateCloudApiTest
 */
class OAuth2PrivateCloudApiTest extends OAuth2ProtectedApiTest {

    protected function getScopes()
    {
        $scope = array(
            sprintf('%s/private-clouds/read',$this->current_realm)
        );

        return $scope;
    }

    public function testGetPrivateClouds(){

        $params  = array(
            'page'     => 1 ,
            'per_page' => 10,
            'status'   => ICompanyServiceRepository::Status_active,
        );

        $headers = array("HTTP_Authorization" => " Bearer " .$this->access_token);
        $response = $this->action("GET", "OAuth2PrivateCloudApiController@getClouds",
            $params,
            array(),
            array(),
            $headers);


        $content = $response->getContent();
        $clouds  = json_decode($content);

        $this->assertResponseStatus(200);
    }

    public function testGetPrivateCloudNotFound(){

        $params  = array(
            'id' => 0
        );

        $headers = array("HTTP_Authorization" => " Bearer " .$this->access_token);
        $response = $this->action("GET", "OAuth2PrivateCloudApiController@getCloud",
            $params,
            array(),
            array(),
            $headers);


        $content = $response->getContent();
        $res     = json_decode($content);

        $this->assertResponseStatus(404);
    }

    public function testGetPrivateCloudFound(){

        $params  = array(
            'id' => 60
        );

        $headers = array("HTTP_Authorization" => " Bearer " .$this->access_token);
        $response = $this->action("GET", "OAuth2PrivateCloudApiController@getCloud",
            $params,
            array(),
            array(),
            $headers);


        $content = $response->getContent();
        $res     = json_decode($content);

        $this->assertResponseStatus(200);
    }

    public function testGetDataCenterRegions(){

        $params  = array(
            'id' => 60
        );

        $headers = array("HTTP_Authorization" => " Bearer " .$this->access_token);
        $response = $this->action("GET", "OAuth2PrivateCloudApiController@getCloudDataCenters",
            $params,
            array(),
            array(),
            $headers);


        $content = $response->getContent();
        $res     = json_decode($content);

        $this->assertResponseStatus(200);

    }
}