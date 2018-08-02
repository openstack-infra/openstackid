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
use Auth\User;
use Illuminate\Support\Facades\App;
use jwa\JSONWebSignatureAndEncryptionAlgorithms;
use jwe\IJWE;
use jwk\impl\OctetSequenceJWKFactory;
use jwk\impl\OctetSequenceJWKSpecification;
use jwk\impl\RSAJWKFactory;
use jwk\impl\RSAJWKPEMPrivateKeySpecification;
use jwk\JSONWebKeyPublicKeyUseValues;
use jws\IJWS;
use jws\impl\specs\JWS_ParamsSpecification;
use jws\JWSFactory;
use jwt\RegisteredJWTClaimNames;
use jwt\utils\JWTClaimSetFactory;
use OAuth2\Models\ClientAssertionAuthenticationContext;
use OAuth2\OAuth2Protocol;
use utils\factories\BasicJWTFactory;
use utils\json_types\StringOrURI;
use Utils\Services\IAuthService;
use Utils\Services\UtilsServiceCatalog;
use jwt\impl\UnsecuredJWT;
/**
 * Class OIDCProtocolTest
 * http://openid.net/wordpress-content/uploads/2015/02/OpenID-Connect-Conformance-Profiles.pdf
 */
class OIDCProtocolTest extends OpenStackIDBaseTest
{
    /**
     * @var string
     */
    private $current_realm;

    protected function prepareForTests()
    {
        parent::prepareForTests();
        App::singleton(UtilsServiceCatalog::ServerConfigurationService, 'StubServerConfigurationService');
        $this->current_realm = Config::get('app.url');
        Session::start();
    }

    public function testNonePrompt()
    {

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';

        $params = array(
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope' => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_None
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
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

        $this->assertTrue(array_key_exists('error', $output));
        $this->assertTrue(!empty($output['error']));
        $this->assertTrue($output['error'] === OAuth2Protocol::OAuth2Protocol_Error_Interaction_Required);

    }

    public function testLoginWithTrailingSpace()
    {
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope' => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_LoginHint => ' sebastian@tipit.net ',
            OAuth2Protocol::OAuth2Protocol_MaxAge    => 3200,
            OAuth2Protocol::OAuth2Protocol_Prompt    => OAuth2Protocol::OAuth2Protocol_Prompt_Consent,
            OAuth2Protocol::OAuth2Protocol_Display   => OAuth2Protocol::OAuth2Protocol_Display_Native
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();

        $response = $this->call('GET', $url);

        $this->assertResponseStatus(412);

        // do login
        $response = $this->action('POST', "UserController@postLogin",
            array
            (
                'username' => ' sebastian@tipit.net ',
                'password' => ' 1qaz2wsx ',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);
    }

    public function testConsentPrompt()
    {
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope' => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200,
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_Consent
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
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
        $response = $this->action('POST', "UserController@postLogin",
            array
            (
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        //do consent
        $url = $response->getTargetUrl();

        $response = $this->action('POST', "UserController@postConsent", array(
            'trust' => IAuthService::AuthorizationResponse_DenyOnce,
            '_token' => Session::token()
        ));

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
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

        $this->assertTrue(array_key_exists('error', $output));
        $this->assertTrue(!empty($output['error']));
        $this->assertTrue($output['error'] === OAuth2Protocol::OAuth2Protocol_Error_Consent_Required);

    }

    public function testConsentLogin()
    {
        //already logged user
        $user = User::where('identifier', '=', 'sebastian.marcet')->first();
        $this->be($user, 'web');

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope' => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200,
            OAuth2Protocol::OAuth2Protocol_Prompt => sprintf('%s %s', OAuth2Protocol::OAuth2Protocol_Prompt_Consent, OAuth2Protocol::OAuth2Protocol_Prompt_Login)
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
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
        $response = $this->action('POST', "UserController@postLogin",
            array
            (
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        //do consent
        $url = $response->getTargetUrl();

        $response = $this->action('POST', "UserController@postConsent", array(
            'trust' => IAuthService::AuthorizationResponse_DenyOnce,
            '_token' => Session::token()
        ));

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
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

        $this->assertTrue(array_key_exists('error', $output));
        $this->assertTrue(!empty($output['error']));
        $this->assertTrue($output['error'] === OAuth2Protocol::OAuth2Protocol_Error_Consent_Required);

    }

    public function testAuthCode()
    {

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope' => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
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
        $response = $this->action('POST', "UserController@postLogin",
            array
            (
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        //do consent
        $url = $response->getTargetUrl();

        $response = $this->action('POST', "UserController@postConsent", array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
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

        $this->assertTrue(array_key_exists('code', $output));
        $this->assertTrue(!empty($output['code']));

    }

    public function testAuthCodeInvalidLoginHint()
    {

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope' => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@sebastian.net',
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();


        $url = $response->getTargetUrl();

        $this->assertTrue(str_contains($url, '/login'));

    }

    public function testAuthCodeOpenIdScopeOnly()
    {

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $client_secret = 'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhg';

        $params = array
        (
            'scope' => 'openid',
            'state' => 'KtWzJk5Vmk8CZwC0',
            'redirect_uri' => 'https://op.certification.openid.net:60393/authz_cb',
            'response_type' => 'code',
            'client_id' => $client_id,
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();

        $response = $this->call('GET', $url);

        $this->assertResponseStatus(200);

        // do login
        $response = $this->action('POST', "UserController@postLogin",
            array
            (
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        //do consent
        $url = $response->getTargetUrl();

        $response = $this->action('POST', "UserController@postConsent", array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
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

        $this->assertTrue(array_key_exists('code', $output));
        $this->assertTrue(!empty($output['code']));

    }

    public function testMaxAge1AndWait2()
    {
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope' => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_MaxAge => 1
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
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
        $response = $this->action('POST', "UserController@postLogin",
            array
            (
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        sleep(2);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $this->assertTrue($response->getTargetUrl() === URL::action("UserController@postLogin"));
    }

    public function testToken
    (
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client',
        $client_secret = 'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhg',
        $use_enc = true
    ) {


        $params = array(
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope' => sprintf('%s profile email address %s', OAuth2Protocol::OpenIdConnect_Scope,
                OAuth2Protocol::OfflineAccess_Scope),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Nonce => 'test_nonce',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_Consent,
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
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
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $response = $this->action('GET', 'UserController@getConsent');

        $this->assertResponseStatus(200);

        $response = $this->action('POST', 'UserController@getConsent', array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
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

        $this->assertTrue(array_key_exists('code', $output));
        $this->assertTrue(!empty($output['code']));

        $params = array(
            'code' => $output['code'],
            'redirect_uri' => 'https://www.test.com/oauth2',
            'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            array(),
            // Symfony interally prefixes headers with "HTTP", so
            array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));


        $this->assertResponseStatus(200);

        $this->assertEquals('application/json;charset=UTF-8', $response->headers->get('Content-Type'));

        $content = $response->getContent();

        $response = json_decode($content);
        $access_token = $response->access_token;
        $refresh_token = $response->refresh_token;
        $id_token = $response->id_token;

        $this->assertTrue(!empty($access_token));
        $this->assertTrue(!empty($refresh_token));
        $this->assertTrue(!empty($id_token));

        $jwt = BasicJWTFactory::build($id_token);

        if ($use_enc) {
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

        return $access_token;
    }

    public function testTokenSeveralScopes
    (
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client',
        $client_secret = 'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhg',
        $use_enc = true
    ) {


        $params = array(
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope' =>
                join(" ", [
                    OAuth2Protocol::OpenIdConnect_Scope,
                    'profile',
                    'email',
                    'address',
                    OAuth2Protocol::OfflineAccess_Scope,
                    sprintf('%s/resource-server/read', $this->current_realm),
                    sprintf('%s/resource-server/read.page', $this->current_realm),
                    sprintf('%s/resource-server/write', $this->current_realm),
                    sprintf('%s/resource-server/delete', $this->current_realm),
                    sprintf('%s/resource-server/update', $this->current_realm),
                    sprintf('%s/resource-server/update.status', $this->current_realm),
                    sprintf('%s/resource-server/regenerate.secret', $this->current_realm),
                ]),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Nonce => 'test_nonce',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_Consent,
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
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
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token'   => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $response = $this->action('GET', 'UserController@getConsent');

        $this->assertResponseStatus(200);

        $response = $this->action('POST', 'UserController@getConsent', array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
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

        $this->assertTrue(array_key_exists('code', $output));
        $this->assertTrue(!empty($output['code']));

        $params = array(
            'code' => $output['code'],
            'redirect_uri' => 'https://www.test.com/oauth2',
            'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            array(),
            // Symfony interally prefixes headers with "HTTP", so
            array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));


        $this->assertResponseStatus(200);

        $this->assertEquals('application/json;charset=UTF-8', $response->headers->get('Content-Type'));

        $content = $response->getContent();

        $response = json_decode($content);
        $access_token = $response->access_token;
        $refresh_token = $response->refresh_token;
        $id_token = $response->id_token;

        $this->assertTrue(!empty($access_token));
        $this->assertTrue(!empty($refresh_token));
        $this->assertTrue(!empty($id_token));

        $jwt = BasicJWTFactory::build($id_token);

        if ($use_enc) {
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

        return $access_token;
    }

    public function testGetRefreshTokenWithPromptSetToConsentLogin(){

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $client_secret = 'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhg';
        $use_enc = true;

        $params = array(
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope' => sprintf('%s profile email address %s', OAuth2Protocol::OpenIdConnect_Scope,
                OAuth2Protocol::OfflineAccess_Scope),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Nonce => 'test_nonce',
            OAuth2Protocol::OAuth2Protocol_Prompt => sprintf('%s %s',OAuth2Protocol::OAuth2Protocol_Prompt_Login, OAuth2Protocol::OAuth2Protocol_Prompt_Consent),
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
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
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $response = $this->action('GET', 'UserController@getConsent');

        $this->assertResponseStatus(200);

        $response = $this->action('POST', 'UserController@getConsent', array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
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

        $this->assertTrue(array_key_exists('code', $output));
        $this->assertTrue(!empty($output['code']));

        $params = array(
            'code' => $output['code'],
            'redirect_uri' => 'https://www.test.com/oauth2',
            'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            array(),
            // Symfony interally prefixes headers with "HTTP", so
            array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));


        $this->assertResponseStatus(200);

        $this->assertEquals('application/json;charset=UTF-8', $response->headers->get('Content-Type'));

        $content = $response->getContent();

        $response = json_decode($content);
        $access_token = $response->access_token;
        $refresh_token = $response->refresh_token;
        $id_token = $response->id_token;

        $this->assertTrue(!empty($access_token));
        $this->assertTrue(!empty($refresh_token));
        $this->assertTrue(!empty($id_token));

        $jwt = BasicJWTFactory::build($id_token);

        if ($use_enc) {
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

    public function testFlowNativeDisplay(){

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $client_secret = 'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhg';

        $params = array(
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope' => sprintf('%s profile email address %s', OAuth2Protocol::OpenIdConnect_Scope, OAuth2Protocol::OfflineAccess_Scope),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Nonce => 'test_nonce',
            OAuth2Protocol::OAuth2Protocol_Prompt => sprintf('%s %s',OAuth2Protocol::OAuth2Protocol_Prompt_Login, OAuth2Protocol::OAuth2Protocol_Prompt_Consent),
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200,
            OAuth2Protocol::OAuth2Protocol_Display   => OAuth2Protocol::OAuth2Protocol_Display_Native
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $response = $this->call('GET', $response->getTargetUrl());

        $this->assertResponseStatus(412);

        $json_response = json_decode($response->getContent(),true);

        // do login
        $response = $this->call($json_response['method'], $json_response['url'],
            array
            (
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => $json_response['required_params_valid_values']["_token"]
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $response = $this->action('GET', 'UserController@getConsent');

        $this->assertResponseStatus(412);

        $json_response = json_decode($response->getContent(),true);

        $response = $this->call($json_response['method'], $json_response['url'], array(
            'trust' => 'AllowOnce',
            '_token' =>  $json_response['required_params_valid_values']["_token"]
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
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

        $this->assertTrue(array_key_exists('code', $output));
        $this->assertTrue(!empty($output['code']));

        $params = array(
            'code' => $output['code'],
            'redirect_uri' => 'https://www.test.com/oauth2',
            'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            array(),
            // Symfony interally prefixes headers with "HTTP", so
            array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));


        $this->assertResponseStatus(200);

        $this->assertEquals('application/json;charset=UTF-8', $response->headers->get('Content-Type'));

        $content = $response->getContent();

        $response = json_decode($content);
        $access_token = $response->access_token;
        $refresh_token = $response->refresh_token;
        $id_token = $response->id_token;

        $this->assertTrue(!empty($access_token));
        $this->assertTrue(!empty($refresh_token));
        $this->assertTrue(!empty($id_token));
    }

    public function testGetRefreshTokenFromNativeAppNTimes($n=5)
    {
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwKlfSyQ3x.android.openstack.client';
        $client_secret = '11c/6Y5N7kOtGKhg11c/6Y5N7kOtGKhg11c/6Y5N7kOtGKhg11c/6Y5N7kOtGKhgfdfdfdf';

        $params_auth_code = array
        (
            'client_id'                              => $client_id,
            'redirect_uri'                           => 'androipapp://oidc_endpoint_callback',
            'response_type'                          => 'code',
            'scope'                                  => sprintf('%s profile email address %s', OAuth2Protocol::OpenIdConnect_Scope, OAuth2Protocol::OfflineAccess_Scope),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Nonce     => 'test_nonce',
            OAuth2Protocol::OAuth2Protocol_Prompt    => OAuth2Protocol::OAuth2Protocol_Prompt_Consent,
            OAuth2Protocol::OAuth2Protocol_MaxAge    => 3200,
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
            $params_auth_code,
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
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $response = $this->action('GET', 'UserController@getConsent');

        $this->assertResponseStatus(200);

        $response = $this->action('POST', 'UserController@getConsent', array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
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

        $this->assertTrue(array_key_exists('code', $output));
        $this->assertTrue(!empty($output['code']));

        $params = array(
            'code' => $output['code'],
            'redirect_uri' => 'androipapp://oidc_endpoint_callback',
            'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            array(),
            // Symfony interally prefixes headers with "HTTP", so
            array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));


        $this->assertResponseStatus(200);

        $this->assertEquals('application/json;charset=UTF-8', $response->headers->get('Content-Type'));

        $content = $response->getContent();

        $response = json_decode($content);
        $access_token = $response->access_token;
        $refresh_token = $response->refresh_token;
        $id_token = $response->id_token;

        $this->assertTrue(!empty($access_token));
        $this->assertTrue(!empty($refresh_token));
        $this->assertTrue(!empty($id_token));


        $iteration = 0;
        do {
            $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
                $params_auth_code,
                array(),
                array(),
                array());


            $this->assertResponseStatus(302);


            $response = $this->action('GET', 'UserController@getConsent');

            $this->assertResponseStatus(200);

            $response = $this->action('POST', 'UserController@getConsent', array(
                'trust' => 'AllowOnce',
                '_token' => Session::token()
            ));

            $this->assertResponseStatus(302);

            // get auth code

            $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
                array(),
                array(),
                array(),
                array());

            $this->assertResponseStatus(302);

            $url = $response->getTargetUrl();
            $this->assertResponseStatus(302);

            $url = $response->getTargetUrl();

            $comps = @parse_url($url);
            $query = $comps['query'];
            $output = array();
            parse_str($query, $output);

            $this->assertTrue(array_key_exists('code', $output));
            $this->assertTrue(!empty($output['code']));

            $params = array(
                'code'         => $output['code'],
                'redirect_uri' => 'androipapp://oidc_endpoint_callback',
                'grant_type'   => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
            );

            $response = $this->action("POST", "OAuth2\OAuth2ProviderController@token",
                $params,
                array(),
                array(),
                array(),
                // Symfony interally prefixes headers with "HTTP", so
                array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));


            $this->assertResponseStatus(200);

            $this->assertEquals('application/json;charset=UTF-8', $response->headers->get('Content-Type'));

            $content = $response->getContent();

            $response = json_decode($content);
            $access_token = $response->access_token;
            $refresh_token = $response->refresh_token;
            $id_token = $response->id_token;

            $this->assertTrue(!empty($access_token));
            $this->assertTrue(!empty($refresh_token));
            $this->assertTrue(!empty($id_token));
            ++$iteration;
        }while( $iteration < $n);
    }

    public function testTokenResponseModePost()
    {

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $client_secret = 'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhg';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope' => sprintf('%s profile email %s', OAuth2Protocol::OpenIdConnect_Scope,
                OAuth2Protocol::OfflineAccess_Scope),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_Consent,
            OAuth2Protocol::OAuth2Protocol_MaxAge => 1,
            OAuth2Protocol::OAuth2Protocol_ResponseMode => OAuth2Protocol::OAuth2Protocol_ResponseMode_FormPost
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
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
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $response = $this->action('GET', 'UserController@getConsent');

        $this->assertResponseStatus(200);

        $response = $this->action('POST', 'UserController@getConsent', array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(200);
        $content = $response->getContent();
        $this->assertEquals('application/x-www-form-urlencoded', $response->headers->get('Content-Type'));

        $output = array();
        parse_str($content, $output);

        $this->assertTrue(array_key_exists('code', $output));
        $this->assertTrue(!empty($output['code']));

        $params = array
        (
            'code' => $output['code'],
            'redirect_uri' => 'https://www.test.com/oauth2',
            'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
        );


        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            array(),
            // Symfony interally prefixes headers with "HTTP", so
            array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

        $this->assertResponseStatus(200);

        $this->assertEquals('application/json;charset=UTF-8', $response->headers->get('Content-Type'));

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

        $payload = $jwt->getPayload();

        $claims_set = $payload->getClaimSet();
    }

    public function testNativeClientBasicAuth()
    {

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwKlfSyQ3x.android.openstack.client';
        $client_secret = '11c/6Y5N7kOtGKhg11c/6Y5N7kOtGKhg11c/6Y5N7kOtGKhg11c/6Y5N7kOtGKhgfdfdfdf';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'androipapp://oidc_endpoint_callback',
            'response_type' => 'code',
            'scope' => sprintf('%s profile email %s', OAuth2Protocol::OpenIdConnect_Scope,
                OAuth2Protocol::OfflineAccess_Scope),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_Consent,
            OAuth2Protocol::OAuth2Protocol_MaxAge => 10000,
            OAuth2Protocol::OAuth2Protocol_ResponseMode => OAuth2Protocol::OAuth2Protocol_ResponseMode_FormPost
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
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
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $response = $this->action('GET', 'UserController@getConsent');

        $this->assertResponseStatus(200);

        $response = $this->action('POST', 'UserController@getConsent', array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(200);
        $content = $response->getContent();
        $this->assertEquals('application/x-www-form-urlencoded', $response->headers->get('Content-Type'));

        $output = array();
        parse_str($content, $output);

        $this->assertTrue(array_key_exists('code', $output));
        $this->assertTrue(!empty($output['code']));

        $params = array
        (
            'code' => $output['code'],
            'redirect_uri' => 'androipapp://oidc_endpoint_callback',
            'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
        );


        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            array(),
            // Symfony interally prefixes headers with "HTTP", so
            array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

        $this->assertResponseStatus(200);

        $this->assertEquals('application/json;charset=UTF-8', $response->headers->get('Content-Type'));

        $content = $response->getContent();

        $response = json_decode($content);
        $access_token = $response->access_token;
        $refresh_token = $response->refresh_token;
        $id_token = $response->id_token;

        $this->assertTrue(!empty($access_token));
        $this->assertTrue(!empty($refresh_token));
        $this->assertTrue(!empty($id_token));

        $jwt = BasicJWTFactory::build($id_token);

        $this->assertTrue($jwt instanceof UnsecuredJWT);

        $claims_set = $jwt->getClaimSet();

        $this->assertTrue(!is_null($claims_set));

        $params = array(
            'client_id' => $client_id,
            'redirect_uri' => 'androipapp://oidc_endpoint_callback',
            'response_type' => 'code',
            'scope' => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_IDTokenHint => $jwt->toCompactSerialization(),
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_None
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
            $params,
            array(),
            array(),
            array());
    }

    public function testClientAuthenticationClientSecretJwt()
    {
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x2.openstack.client';
        $client_secret = 'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhg';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope' => sprintf('%s profile email %s', OAuth2Protocol::OpenIdConnect_Scope,
                OAuth2Protocol::OfflineAccess_Scope),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_Consent,
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
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
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $response = $this->action('GET', 'UserController@getConsent');

        $this->assertResponseStatus(200);

        $response = $this->action('POST', 'UserController@getConsent', array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
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

        $this->assertTrue(array_key_exists('code', $output));
        $this->assertTrue(!empty($output['code']));

        $now = time();

        $claim_set = JWTClaimSetFactory::build
        (
            array
            (
                RegisteredJWTClaimNames::Issuer => $client_id,
                RegisteredJWTClaimNames::Subject => $client_id,
                RegisteredJWTClaimNames::Audience => URL::action("OAuth2\OAuth2ProviderController@token"),
                RegisteredJWTClaimNames::JWTID => '123456789',
                RegisteredJWTClaimNames::ExpirationTime => $now + 3600,
                RegisteredJWTClaimNames::IssuedAt => $now
            )
        );

        $key = OctetSequenceJWKFactory::build
        (
            new OctetSequenceJWKSpecification
            (
                $client_secret,
                JSONWebSignatureAndEncryptionAlgorithms::HS512
            )
        );

        $alg = new StringOrURI(JSONWebSignatureAndEncryptionAlgorithms::HS512);

        $jws = JWSFactory::build
        (
            new JWS_ParamsSpecification
            (
                $key,
                $alg,
                $claim_set
            )
        );

        $params = array
        (
            'code' => $output['code'],
            'redirect_uri' => 'https://www.test.com/oauth2',
            'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
            OAuth2Protocol::OAuth2Protocol_ClientAssertionType => ClientAssertionAuthenticationContext::RegisteredAssertionType,
            OAuth2Protocol::OAuth2Protocol_ClientAssertion => $jws->toCompactSerialization()
        );


        $response = $this->action
        (
            "POST",
            "OAuth2\OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            array()
        );

        $this->assertResponseStatus(200);
        $this->assertEquals('application/json;charset=UTF-8', $response->headers->get('Content-Type'));
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

    public function testClientAuthenticationPrivateKeyJwt()
    {
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwKlfSyQ3x.android2.openstack.client';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'androipapp://oidc_endpoint_callback2',
            'response_type' => 'code',
            'scope' => sprintf('%s profile email %s', OAuth2Protocol::OpenIdConnect_Scope,
                OAuth2Protocol::OfflineAccess_Scope),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_Consent,
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
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
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $response = $this->action('GET', 'UserController@getConsent');

        $this->assertResponseStatus(200);

        $response = $this->action('POST', 'UserController@getConsent', array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
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

        $this->assertTrue(array_key_exists('code', $output));
        $this->assertTrue(!empty($output['code']));

        $now = time();

        $claim_set = JWTClaimSetFactory::build
        (
            array
            (
                RegisteredJWTClaimNames::Issuer => $client_id,
                RegisteredJWTClaimNames::Subject => $client_id,
                RegisteredJWTClaimNames::Audience => URL::action("OAuth2\OAuth2ProviderController@token"),
                RegisteredJWTClaimNames::JWTID => '123456789',
                RegisteredJWTClaimNames::ExpirationTime => $now + 3600,
                RegisteredJWTClaimNames::IssuedAt => $now
            )
        );

        $key = RSAJWKFactory::build
        (
            new RSAJWKPEMPrivateKeySpecification
            (
                TestSeeder::$client_private_key_2,
                RSAJWKPEMPrivateKeySpecification::WithoutPassword,
                JSONWebSignatureAndEncryptionAlgorithms::RS512
            )
        );

        $key->setId('public_key_44');

        $alg = new StringOrURI(JSONWebSignatureAndEncryptionAlgorithms::RS512);

        $jws = JWSFactory::build
        (
            new JWS_ParamsSpecification
            (
                $key,
                $alg,
                $claim_set
            )
        );

        $params = array
        (
            'code' => $output['code'],
            'redirect_uri' => 'androipapp://oidc_endpoint_callback2',
            'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
            OAuth2Protocol::OAuth2Protocol_ClientAssertionType => ClientAssertionAuthenticationContext::RegisteredAssertionType,
            OAuth2Protocol::OAuth2Protocol_ClientAssertion => $jws->toCompactSerialization()
        );


        $response = $this->action
        (
            "POST",
            "OAuth2\OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            array()
        );

        $this->assertResponseStatus(200);
        $this->assertEquals('application/json;charset=UTF-8', $response->headers->get('Content-Type'));
        $content = $response->getContent();

        $response = json_decode($content);
        $access_token = $response->access_token;
        $id_token = $response->id_token;

        $this->assertTrue(!empty($access_token));
        $this->assertTrue(!empty($id_token));
    }

    public function testImplicitFlowTokenIdToken()
    {
        // use a public client

        $client_id = '1234/Vcvr6fvQbH4HyNgwKlfSyQ3x.openstack.client';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => OAuth2Protocol::OAuth2Protocol_ResponseType_IdToken . OAuth2Protocol::OAuth2Protocol_ResponseType_Delimiter . OAuth2Protocol::OAuth2Protocol_ResponseType_Token,
            'scope' => sprintf('%s profile email %s', OAuth2Protocol::OpenIdConnect_Scope,
                OAuth2Protocol::OfflineAccess_Scope),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_Consent,
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200,
            OAuth2Protocol::OAuth2Protocol_Nonce => 'ctqg5FeNoYnZ',
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
            $params,
            array(),
            array(),
            array());


        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();
        // do login
        $response = $this->call('POST', $url,
            array
            (
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());


        $response = $this->action('POST', 'UserController@getConsent', array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get response

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();
        $comps = @parse_url($url);
        $fragment = $comps['fragment'];

        $this->assertTrue(!empty($fragment));
        $output = array();
        parse_str($fragment, $output);

        $this->assertTrue(array_key_exists('access_token', $output));
        $this->assertTrue(!empty($output['access_token']));
        $this->assertTrue(array_key_exists('id_token', $output));
        $this->assertTrue(!empty($output['id_token']));

    }

    public function testImplicitFlowIdToken()
    {
        // use a public client

        $client_id = '1234/Vcvr6fvQbH4HyNgwKlfSyQ3x.openstack.client';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => OAuth2Protocol::OAuth2Protocol_ResponseType_IdToken,
            'scope' => sprintf('%s profile email %s', OAuth2Protocol::OpenIdConnect_Scope,
                OAuth2Protocol::OfflineAccess_Scope),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_Consent,
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200,
            OAuth2Protocol::OAuth2Protocol_Nonce => 'ctqg5FeNoYnZ',
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
            $params,
            array(),
            array(),
            array());


        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();
        // do login
        $response = $this->call('POST', $url,
            array
            (
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );


        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());


        $response = $this->action('POST', 'UserController@getConsent', array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get response

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();
        $comps = @parse_url($url);
        $fragment = $comps['fragment'];

        $this->assertTrue(!empty($fragment));
        $output = array();
        parse_str($fragment, $output);

        $this->assertTrue(!array_key_exists('access_token', $output));
        $this->assertTrue(empty($output['access_token']));
        $this->assertTrue(array_key_exists('id_token', $output));
        $this->assertTrue(!empty($output['id_token']));
    }

    public function testImplicitFlowIdTokenMaxAge1000()
    {
        // use a public client

        $client_id = '1234/Vcvr6fvQbH4HyNgwKlfSyQ3x.openstack.client';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => OAuth2Protocol::OAuth2Protocol_ResponseType_IdToken,
            'scope' =>join(' ', [
                OAuth2Protocol::OpenIdConnect_Scope,
                'profile',
                'email',
                OAuth2Protocol::OfflineAccess_Scope
            ]),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_Consent,
            OAuth2Protocol::OAuth2Protocol_MaxAge => 1000,
            OAuth2Protocol::OAuth2Protocol_Nonce => 'ctqg5FeNoYnZ',
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
            $params,
            array(),
            array(),
            array());


        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();
        // do login
        $response = $this->call('POST', $url,
            array
            (
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());


        $response = $this->action('POST', 'UserController@getConsent', array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get response

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();
        $comps = @parse_url($url);
        $fragment = $comps['fragment'];

        $this->assertTrue(!empty($fragment));
        $output = array();
        parse_str($fragment, $output);

        $this->assertTrue(!array_key_exists('access_token', $output));
        $this->assertTrue(empty($output['access_token']));
        $this->assertTrue(array_key_exists('id_token', $output));
        $this->assertTrue(!empty($output['id_token']));

        sleep(10);

        $params[OAuth2Protocol::OAuth2Protocol_Prompt] = OAuth2Protocol::OAuth2Protocol_Prompt_None;
        $params['scope'] =join(' ', [
            OAuth2Protocol::OpenIdConnect_Scope,
            'profile',
            'email',
        ]);

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();
        $comps = @parse_url($url);
        $fragment = $comps['fragment'];

        $this->assertTrue(!empty($fragment));
        $output2 = array();
        parse_str($fragment, $output2);

        $this->assertTrue(!array_key_exists('access_token', $output2));
        $this->assertTrue(empty($output2['access_token']));
        $this->assertTrue(array_key_exists('id_token', $output2));
        $this->assertTrue(!empty($output2['id_token']));
    }

    public function testImplicitFlowAccessToken()
    {
        // use a public client

        $client_id = '1234/Vcvr6fvQbH4HyNgwKlfSyQ3x.openstack.client';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => OAuth2Protocol::OAuth2Protocol_ResponseType_Token,
            'scope' => sprintf('%s profile email %s', OAuth2Protocol::OpenIdConnect_Scope,
                OAuth2Protocol::OfflineAccess_Scope),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_Consent,
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200,
            OAuth2Protocol::OAuth2Protocol_Nonce => 'ctqg5FeNoYnZ',
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
            $params,
            array(),
            array(),
            array());


        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();
        // do login
        $response = $this->call('POST', $url,
            array
            (
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );


        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());


        $response = $this->action('POST', 'UserController@getConsent', array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get response

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();
        $comps = @parse_url($url);
        $fragment = $comps['fragment'];

        $this->assertTrue(!empty($fragment));
        $output = array();
        parse_str($fragment, $output);

        $this->assertTrue(array_key_exists('access_token', $output));
        $this->assertTrue(!empty($output['access_token']));
        $this->assertTrue(!array_key_exists('id_token', $output));
        $this->assertTrue(empty($output['id_token']));

    }

    public function testImplicitFlowAccessTokenAbsentUserError()
    {
        // use a public client

        //already logged user
        $user = User::where('identifier', '=', 'sebastian.marcet')->first();
        $this->be($user, 'web');

        $client_id = '1234/Vcvr6fvQbH4HyNgwKlfSyQ3x.openstack.client';
        $scopes = sprintf('%s profile email', OAuth2Protocol::OpenIdConnect_Scope);

        $client = \Models\OAuth2\Client::where('client_id', '=', $client_id)->first();

        $former_consent = new \Models\OAuth2\UserConsent();
        $former_consent->client_id = $client->id;
        $former_consent->user_id   = $user->id;
        $former_consent->scopes    = $scopes;
        $former_consent->Save();

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => sprintf("%s %s", OAuth2Protocol::OAuth2Protocol_ResponseType_Token, OAuth2Protocol::OAuth2Protocol_IdToken),
            'scope' => $scopes,
            OAuth2Protocol::OAuth2Protocol_Nonce => 'ctqg5FeNoYnZ',
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();

        $comps = @parse_url($url);
        $fragment = $comps['fragment'];

        $this->assertTrue(!empty($fragment));
        $output = array();
        parse_str($fragment, $output);

        $this->assertTrue(array_key_exists('access_token', $output));
        $this->assertTrue(!empty($output['access_token']));
        $this->assertTrue(array_key_exists('id_token', $output));
        $this->assertTrue(!empty($output['id_token']));

    }

    public function testImplicitFlowResponseModePost()
    {
        // use a public client

        $client_id = '1234/Vcvr6fvQbH4HyNgwKlfSyQ3x.openstack.client';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => OAuth2Protocol::OAuth2Protocol_ResponseType_IdToken . OAuth2Protocol::OAuth2Protocol_ResponseType_Delimiter . OAuth2Protocol::OAuth2Protocol_ResponseType_Token,
            'scope' => sprintf('%s profile email %s', OAuth2Protocol::OpenIdConnect_Scope,
                OAuth2Protocol::OfflineAccess_Scope),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_Consent,
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200,
            OAuth2Protocol::OAuth2Protocol_ResponseMode => OAuth2Protocol::OAuth2Protocol_ResponseMode_FormPost,
            OAuth2Protocol::OAuth2Protocol_Nonce => 'ctqg5FeNoYnZ',
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
            $params,
            array(),
            array(),
            array());


        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();
        // do login
        $response = $this->call('POST', $url,
            array
            (
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());


        $response = $this->action('POST', 'UserController@getConsent', array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get response

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(200);

        $this->assertEquals('application/x-www-form-urlencoded', $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertTrue(!empty($content));
        $output = array();
        parse_str($content, $output);

        $this->assertTrue(array_key_exists('access_token', $output));
        $this->assertTrue(!empty($output['access_token']));
        $this->assertTrue(array_key_exists('id_token', $output));
        $this->assertTrue(!empty($output['id_token']));

    }

    public function testUserInfoEndpointGETAndBearerHeader()
    {
        $access_token = $this->testToken();
        $response = $this->action(
            "GET",
            "Api\OAuth2\OAuth2UserApiController@userInfo",
            array(),
            array(),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " . $access_token));

        $this->assertResponseStatus(200);
        $content = $response->getContent();
        $this->assertTrue(!empty($content));
        $user_info = json_decode($content, true);

        $this->assertTrue(isset($user_info['sub']));
    }

    public function testUserInfoEndpointPOSTAndBearerHeader()
    {
        $access_token = $this->testToken();
        $response = $this->action("POST", "Api\OAuth2\OAuth2UserApiController@userInfo",
            array(),
            array(),
            array(),
            array(),
            array("HTTP_Authorization" => " Bearer " . $access_token));

        $this->assertResponseStatus(200);
        $content = $response->getContent();
        $this->assertTrue(!empty($content));
        $user_info = json_decode($content, true);

        $this->assertTrue(isset($user_info['sub']));
    }

    public function testUserInfoEndpointPOSTAndBearerBody()
    {
        $access_token = $this->testToken();
        $response = $this->action("POST", "Api\OAuth2\OAuth2UserApiController@userInfo",
            array(),
            array
            (
                'access_token' => $access_token
            ),
            array(),
            array());

        $this->assertResponseStatus(200);
        $content = $response->getContent();
        $this->assertTrue(!empty($content));
        $user_info = json_decode($content, true);

        $this->assertTrue(isset($user_info['sub']));
    }

    public function testUserInfoEndpointPOSTAndBearerBodyRS512()
    {
        $access_token = $this->testToken
        (
            'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ33.openstack.client',
            'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N585OtGKhg55',
            false
        );

        $response = $this->action("POST", "Api\OAuth2\OAuth2UserApiController@userInfo",
            array(),
            array
            (
                'access_token' => $access_token
            ),
            array(),
            array());

        $this->assertResponseStatus(200);
        $user_info_response = $response->getContent();

        $this->assertTrue($response->headers->get('content-type') === \utils\http\HttpContentType::JWT);
        $this->assertTrue(!empty($user_info_response));

        $jwt = BasicJWTFactory::build($user_info_response);
        $this->assertTrue($jwt instanceof IJWS);

    }

    public function testHybridFlowCodeIdToken()
    {
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $client_secret = 'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhg';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'id_token code',
            'scope' => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_MaxAge => 1000,
            OAuth2Protocol::OAuth2Protocol_Nonce => 'ctqg5FeNoYnZ',
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
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
        $response = $this->action('POST', "UserController@postLogin",
            array
            (
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        //do consent
        $url = $response->getTargetUrl();

        $response = $this->action('POST', "UserController@postConsent", array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();
        $comps = @parse_url($url);
        $fragment = $comps['fragment'];

        $this->assertTrue(!empty($fragment));
        $output = array();
        parse_str($fragment, $output);

        $this->assertTrue(array_key_exists('code', $output));
        $this->assertTrue(!empty($output['code']));
        $this->assertTrue(!array_key_exists('access_token', $output));
        $this->assertTrue(empty($output['access_token']));
        $this->assertTrue(array_key_exists('id_token', $output));
        $this->assertTrue(!empty($output['id_token']));

        $params = array
        (
            'code' => $output['code'],
            'redirect_uri' => 'https://www.test.com/oauth2',
            'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            array(),
            // Symfony interally prefixes headers with "HTTP", so
            array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

        $this->assertResponseStatus(200);
    }

    public function testHybridFlowCodeIdTokenIdTokenHint()
    {
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $client_secret = 'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhg';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'id_token code',
            'scope' => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_MaxAge => 1000,
            OAuth2Protocol::OAuth2Protocol_Nonce => 'ctqg5FeNoYnZ',
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
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
        $response = $this->action('POST', "UserController@postLogin",
            array
            (
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        //do consent
        $url = $response->getTargetUrl();

        $response = $this->action('POST', "UserController@postConsent", array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();
        $comps = @parse_url($url);
        $fragment = $comps['fragment'];

        $this->assertTrue(!empty($fragment));
        $output = array();
        parse_str($fragment, $output);

        $this->assertTrue(array_key_exists('code', $output));
        $this->assertTrue(!empty($output['code']));
        $this->assertTrue(!array_key_exists('access_token', $output));
        $this->assertTrue(empty($output['access_token']));
        $this->assertTrue(array_key_exists('id_token', $output));
        $this->assertTrue(!empty($output['id_token']));

        $id_token = $output['id_token'];

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


        $jwk = OctetSequenceJWKFactory::build
        (
            new OctetSequenceJWKSpecification
            (
                $client_secret,
                JSONWebSignatureAndEncryptionAlgorithms::HS512
            )
        );

        $jwk->setId('shared_secret');

        $jwt->setKey($jwk);

        $verified = $jwt->verify(JSONWebSignatureAndEncryptionAlgorithms::HS512);

        $this->assertTrue($verified);

        // ok send as id token hint again ....

        $id_token_hint = $jwt->toCompactSerialization();

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'id_token code',
            'scope' => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_None,
            OAuth2Protocol::OAuth2Protocol_IDTokenHint => $id_token_hint,
            OAuth2Protocol::OAuth2Protocol_Nonce => 'ctqg5FeNoYnZ',
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();
        $comps = @parse_url($url);
        $fragment = $comps['fragment'];

        $this->assertTrue(!empty($fragment));
        $output = array();
        parse_str($fragment, $output);

        $this->assertTrue(array_key_exists('code', $output));
        $this->assertTrue(!empty($output['code']));
        $this->assertTrue(!array_key_exists('access_token', $output));
        $this->assertTrue(empty($output['access_token']));
        $this->assertTrue(array_key_exists('id_token', $output));
        $this->assertTrue(!empty($output['id_token']));

        // try with another key client private key signing (RSA)
        $claim_set = $jwt->getPayload()->getClaimSet();

        $key = RSAJWKFactory::build
        (
            new RSAJWKPEMPrivateKeySpecification
            (
                TestSeeder::$client_private_key_2,
                RSAJWKPEMPrivateKeySpecification::WithoutPassword,
                JSONWebSignatureAndEncryptionAlgorithms::RS512
            )
        );

        $key->setId('public_key_2');

        $alg = new StringOrURI(JSONWebSignatureAndEncryptionAlgorithms::RS512);
        $jws = JWSFactory::build( new JWS_ParamsSpecification($key,$alg, $claim_set) );
        // and sign with server private key
        $id_token_hint = $jws->toCompactSerialization();

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'id_token code',
            'scope' => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_None,
            OAuth2Protocol::OAuth2Protocol_IDTokenHint => $id_token_hint,
            OAuth2Protocol::OAuth2Protocol_Nonce => 'ctqg5FeNoYnZ',
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
            $params,
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();
        $comps = @parse_url($url);
        $fragment = $comps['fragment'];

        $this->assertTrue(!empty($fragment));
        $output = array();
        parse_str($fragment, $output);

        $this->assertTrue(array_key_exists('error', $output));
        $this->assertTrue(!empty($output['error']));
        $this->assertTrue($output['error'] == 'server_error');

        $this->assertTrue(array_key_exists('error_description', $output));
        $this->assertTrue(!empty($output['error_description']));
        $this->assertTrue($output['error_description'] == 'original kid public_key_2 - current kid shared_secret');
    }

    public function testHybridFlowCodeAccessToken()
    {
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $client_secret = 'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhg';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code token',
            'scope' => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_MaxAge => 1000,
            OAuth2Protocol::OAuth2Protocol_Nonce => 'ctqg5FeNoYnZ',
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
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
        $response = $this->action('POST', "UserController@postLogin",
            array
            (
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        //do consent
        $url = $response->getTargetUrl();

        $response = $this->action('POST', "UserController@postConsent", array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();
        $comps = @parse_url($url);
        $fragment = $comps['fragment'];

        $this->assertTrue(!empty($fragment));
        $output = array();
        parse_str($fragment, $output);

        $this->assertTrue(array_key_exists('code', $output));
        $this->assertTrue(!empty($output['code']));
        $this->assertTrue(array_key_exists('access_token', $output));
        $this->assertTrue(!empty($output['access_token']));
        $this->assertTrue(!array_key_exists('id_token', $output));
        $this->assertTrue(empty($output['id_token']));

        $params = array
        (
            'code' => $output['code'],
            'redirect_uri' => 'https://www.test.com/oauth2',
            'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            array(),
            // Symfony interally prefixes headers with "HTTP", so
            array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

        $this->assertResponseStatus(200);

        $this->assertEquals('application/json;charset=UTF-8', $response->headers->get('Content-Type'));

        $content = $response->getContent();

        $response = json_decode($content);
        $access_token = $response->access_token;
        $this->assertTrue(!empty($access_token));

        $this->assertTrue($output['access_token'] !== $access_token);

    }

    public function testHybridFlowCodeAccessTokenIdToken()
    {
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $client_secret = 'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhg';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code token id_token',
            'scope' => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_MaxAge => 1000,
            OAuth2Protocol::OAuth2Protocol_Nonce => 'ctqg5FeNoYnZ',
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
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
        $response = $this->action('POST', "UserController@postLogin",
            array
            (
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        //do consent
        $url = $response->getTargetUrl();

        $response = $this->action('POST', "UserController@postConsent", array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();
        $comps = @parse_url($url);
        $fragment = $comps['fragment'];

        $this->assertTrue(!empty($fragment));
        $output = array();
        parse_str($fragment, $output);

        $this->assertTrue(array_key_exists('code', $output));
        $this->assertTrue(!empty($output['code']));
        $this->assertTrue(array_key_exists('access_token', $output));
        $this->assertTrue(!empty($output['access_token']));
        $this->assertTrue(array_key_exists('id_token', $output));
        $this->assertTrue(!empty($output['id_token']));


        $params = array
        (
            'code' => $output['code'],
            'redirect_uri' => 'https://www.test.com/oauth2',
            'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            array(),
            // Symfony interally prefixes headers with "HTTP", so
            array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));

        $this->assertResponseStatus(200);
    }

    public function testTryingAuthCodeTwice()
    {
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $client_secret = 'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhg';

        $params = array
        (
            'client_id' => $client_id,
            'redirect_uri' => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope' => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200,
            OAuth2Protocol::OAuth2Protocol_Nonce => 'ctqg5FeNoYnZ',
        );

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@auth",
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
        $response = $this->action('POST', "UserController@postLogin",
            array
            (
                'username' => 'sebastian@tipit.net',
                'password' => '1qaz2wsx',
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        //do consent
        $url = $response->getTargetUrl();

        $response = $this->action('POST', "UserController@postConsent", array(
            'trust' => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get auth code

        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@auth",
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

        $this->assertTrue(array_key_exists('code', $output));
        $this->assertTrue(!empty($output['code']));


        $params = array(
            'code' => $output['code'],
            'redirect_uri' => 'https://www.test.com/oauth2',
            'grant_type' => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
        );

        // 1st
        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            array(),
            // Symfony interally prefixes headers with "HTTP", so
            array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));


        $this->assertResponseStatus(200);

        $this->assertEquals('application/json;charset=UTF-8', $response->headers->get('Content-Type'));

        $content = $response->getContent();

        $response = json_decode($content);

        // 2nd

        $response = $this->action("POST", "OAuth2\OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            array(),
            // Symfony interally prefixes headers with "HTTP", so
            array("HTTP_Authorization" => " Basic " . base64_encode($client_id . ':' . $client_secret)));


        $this->assertResponseStatus(400);

        $this->assertEquals('application/json;charset=UTF-8', $response->headers->get('Content-Type'));

        $content = $response->getContent();

        $response = json_decode($content);

        $this->assertTrue($response->error === 'invalid_grant');
    }

    public function testDiscovery()
    {
        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@discovery");

        $this->assertResponseStatus(200);

        $this->assertEquals('application/json;charset=UTF-8', $response->headers->get('Content-Type'));

        $content = $response->getContent();

        $this->assertTrue(!empty($content));

        $response = json_decode($content, true);

        $this->assertTrue(isset($response['issuer']));
    }

    public function testJWK()
    {
        $response = $this->action("GET", "OAuth2\OAuth2ProviderController@certs");

        $this->assertResponseStatus(200);

        $this->assertEquals('application/json;charset=UTF-8', $response->headers->get('Content-Type'));

        $content = $response->getContent();

        $this->assertTrue(!empty($content));

        $response = json_decode($content, true);

        $this->assertTrue(isset($response['keys']));
    }

}