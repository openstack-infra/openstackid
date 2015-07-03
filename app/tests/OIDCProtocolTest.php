<?php

/**
 * Copyright 2015 OpenStack Foundation
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
use Illuminate\Support\Facades\App;
use jwe\IJWE;
use jwk\impl\RSAJWKFactory;
use jwk\impl\RSAJWKPEMPrivateKeySpecification;
use jwk\JSONWebKeyPublicKeyUseValues;
use jws\IJWS;
use oauth2\OAuth2Protocol;
use utils\factories\BasicJWTFactory;
use utils\services\UtilsServiceCatalog;

class OIDCProtocolTest extends OpenStackIDBaseTest
{
    private $current_realm;

    protected function prepareForTests()
    {
        parent::prepareForTests();
        App::singleton(UtilsServiceCatalog::ServerConfigurationService, 'StubServerConfigurationService');
        $this->current_realm = Config::get('app.url');
        Route::enableFilters();
        Session::start();
    }

    public function testNonePrompt()
    {

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';

        $params = array(
            'client_id'    => $client_id,
            'redirect_uri'  => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope'         => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_None
        );

        $response = $this->action("POST", "OAuth2ProviderController@authorize",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();

        $comps = @parse_url($url);
        $query = $comps['query'];
        $output = array();
        parse_str($query, $output);

        $this->assertTrue(array_key_exists('error', $output) );
        $this->assertTrue(!empty($output['error']) );
        $this->assertTrue($output['error'] === OAuth2Protocol::OAuth2Protocol_Error_Login_Required );

    }

    /**
     * Get Auth Code Test
     */
    public function testAuthCode()
    {

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';

        $params = array(
            'client_id'    => $client_id,
            'redirect_uri'  => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope'         => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200
        );

        $response = $this->action("POST", "OAuth2ProviderController@authorize",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();

        $response = $this->call('GET', $url);

        $this->assertResponseStatus(200);

        // verify that login hint (email) is populated
        $this->assertTrue(str_contains($response->getContent(), 'sebastian@tipit.net'));

        // do login
        $response = $this->call('POST', $url,
            array
            (
                'username'  => 'sebastian@tipit.net',
                'password'  => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2ProviderController@authorize",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        //do consent
        $url = $response->getTargetUrl();

        $response = $this->call('POST', $url, array(
            'trust'  => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2ProviderController@authorize",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();

        $comps = @parse_url($url);
        $query = $comps['query'];
        $output = array();
        parse_str($query, $output);

        $this->assertTrue(array_key_exists('code', $output) );
        $this->assertTrue(!empty($output['code']) );

    }

    /** Get Token Test
     * @throws Exception
     */
    public function testToken()
    {

        $client_id     = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $client_secret = 'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhg';

        $params = array(
            'client_id'    => $client_id,
            'redirect_uri'  => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope'         => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_AccessType => OAuth2Protocol::OAuth2Protocol_AccessType_Offline,
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200
        );

        $response = $this->action("POST", "OAuth2ProviderController@authorize",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();

        $response = $this->call('GET', $url);

        $this->assertResponseStatus(200);

        // verify that login hint (email) is populated
        $this->assertTrue(str_contains($response->getContent(), 'sebastian@tipit.net'));

        // do login
        $response = $this->call('POST', $url,
            array
            (
                'username'  => 'sebastian@tipit.net',
                'password'  => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2ProviderController@authorize",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        //do consent
        $url = $response->getTargetUrl();

        $response = $this->call('POST', $url, array(
            'trust'  => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2ProviderController@authorize",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();

        $comps = @parse_url($url);
        $query = $comps['query'];
        $output = array();
        parse_str($query, $output);

        $this->assertTrue(array_key_exists('code', $output) );
        $this->assertTrue(!empty($output['code']) );

        $params = array(
            'code' => $output['code'],
            'redirect_uri' => 'https://www.test.com/oauth2',
            'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
        );


        $response = $this->action("POST", "OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            // Symfony interally prefixes headers with "HTTP", so
            array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));


        $this->assertResponseStatus(200);

        $content = $response->getContent();

        $response = json_decode($content);
        $access_token = $response->access_token;
        $refresh_token = $response->refresh_token;
        $id_token = $response->id_token;

        $this->assertTrue(!empty($access_token));
        $this->assertTrue(!empty($refresh_token));
        $this->assertTrue(!empty($id_token));

        $jwt = BasicJWTFactory::build($id_token);

        $this->assertTrue($jwt instanceof IJWE);

        $recipient_key = RSAJWKFactory::build
        (
            new RSAJWKPEMPrivateKeySpecification
            (
                TestSeeder::$client_private_key_1,
                RSAJWKPEMPrivateKeySpecification::WithoutPassword,
                $jwt->getJOSEHeader()->getAlgorithm()->getString()
            )
        );

        $recipient_key->setKeyUse(JSONWebKeyPublicKeyUseValues::Encryption)->setId('recipient_public_key');


        $jwt->setRecipientKey($recipient_key);

        $payload = $jwt->getPlainText();

        $jwt = BasicJWTFactory::build($payload);

        $this->assertTrue($jwt instanceof IJWS);
    }

}