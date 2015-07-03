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
use oauth2\models\ClientAssertionAuthenticationContext;
use oauth2\OAuth2Protocol;
use utils\factories\BasicJWTFactory;
use utils\json_types\StringOrURI;
use utils\services\IAuthService;
use utils\services\UtilsServiceCatalog;
use auth\User;

/**
 * Class OIDCProtocolTest
 */
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

    public function testConsentPrompt()
    {
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';

        $params = array
        (
            'client_id'    => $client_id,
            'redirect_uri'  => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope'         => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200,
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_Consent
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
        $response = $this->action('POST', "UserController@postLogin",
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

        $response = $this->action('POST', "UserController@postConsent", array(
            'trust'  => IAuthService::AuthorizationResponse_DenyOnce,
            '_token' => Session::token()
        ));

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

        $this->assertTrue(array_key_exists('error', $output) );
        $this->assertTrue(!empty($output['error']) );
        $this->assertTrue($output['error'] === OAuth2Protocol::OAuth2Protocol_Error_Consent_Required );

    }

    public function testConsentLogin()
    {
        //already logged user
        $user = User::where('identifier', '=', 'sebastian.marcet')->first();
        $this->be($user);

        //already given consent

        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';

        $params = array
        (
            'client_id'    => $client_id,
            'redirect_uri'  => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope'         => 'openid profile email',
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200,
            OAuth2Protocol::OAuth2Protocol_Prompt => sprintf('%s %s',OAuth2Protocol::OAuth2Protocol_Prompt_Consent, OAuth2Protocol::OAuth2Protocol_Prompt_Login)
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
        $response = $this->action('POST', "UserController@postLogin",
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

        $response = $this->action('POST', "UserController@postConsent", array(
            'trust'  => IAuthService::AuthorizationResponse_DenyOnce,
            '_token' => Session::token()
        ));

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

        $this->assertTrue(array_key_exists('error', $output) );
        $this->assertTrue(!empty($output['error']) );
        $this->assertTrue($output['error'] === OAuth2Protocol::OAuth2Protocol_Error_Consent_Required );

    }

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
        $response = $this->action('POST', "UserController@postLogin",
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

        $response = $this->action('POST', "UserController@postConsent", array(
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

    public function testToken()
    {

        $client_id     = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $client_secret = 'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhg';

        $params = array(
            'client_id'    => $client_id,
            'redirect_uri'  => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope'         => sprintf('%s profile email %s', OAuth2Protocol::OpenIdConnect_Scope, OAuth2Protocol::OfflineAccess_Scope),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_Consent,
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

        $response = $this->action('GET', 'UserController@getConsent');

        $this->assertResponseStatus(200);

        $response = $this->action('POST', 'UserController@getConsent', array(
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

    public function testClientAuthenticationClientSecretJwt()
    {
        $client_id     = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x2.openstack.client';
        $client_secret = 'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhg';

        $params = array
        (
            'client_id'    => $client_id,
            'redirect_uri'  => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope'         => sprintf('%s profile email %s', OAuth2Protocol::OpenIdConnect_Scope, OAuth2Protocol::OfflineAccess_Scope),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_Consent,
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

        $response = $this->action('GET', 'UserController@getConsent');

        $this->assertResponseStatus(200);

        $response = $this->action('POST', 'UserController@getConsent', array(
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

        $now = time();

        $claim_set = JWTClaimSetFactory::build
        (
            array
            (
                RegisteredJWTClaimNames::Issuer         => $client_id,
                RegisteredJWTClaimNames::Subject        => $client_id,
                RegisteredJWTClaimNames::Audience       => URL::action("OAuth2ProviderController@token"),
                RegisteredJWTClaimNames::JWTID          => '123456789',
                RegisteredJWTClaimNames::ExpirationTime => $now+3600,
                RegisteredJWTClaimNames::IssuedAt       => $now
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
            'code'                                             => $output['code'],
            'redirect_uri'                                     => 'https://www.test.com/oauth2',
            'grant_type'                                       => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
            OAuth2Protocol::OAuth2Protocol_ClientAssertionType => ClientAssertionAuthenticationContext::RegisteredAssertionType,
            OAuth2Protocol::OAuth2Protocol_ClientAssertion     => $jws->toCompactSerialization()
        );


        $response = $this->action
        (
            "POST",
            "OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            array()
        );

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

    public function testClientAuthenticationPrivateKeyJwt()
    {
        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwKlfSyQ3x.openstack.client';

        $params = array
        (
            'client_id'    => $client_id,
            'redirect_uri'  => 'https://www.test.com/oauth2',
            'response_type' => 'code',
            'scope'         => sprintf('%s profile email %s', OAuth2Protocol::OpenIdConnect_Scope, OAuth2Protocol::OfflineAccess_Scope),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_Consent,
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

        $response = $this->action('GET', 'UserController@getConsent');

        $this->assertResponseStatus(200);

        $response = $this->action('POST', 'UserController@getConsent', array(
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

        $now = time();

        $claim_set = JWTClaimSetFactory::build
        (
            array
            (
                RegisteredJWTClaimNames::Issuer         => $client_id,
                RegisteredJWTClaimNames::Subject        => $client_id,
                RegisteredJWTClaimNames::Audience       => URL::action("OAuth2ProviderController@token"),
                RegisteredJWTClaimNames::JWTID          => '123456789',
                RegisteredJWTClaimNames::ExpirationTime => $now+3600,
                RegisteredJWTClaimNames::IssuedAt       => $now
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
            'code'                                             => $output['code'],
            'redirect_uri'                                     => 'https://www.test.com/oauth2',
            'grant_type'                                       => OAuth2Protocol::OAuth2Protocol_GrantType_AuthCode,
            OAuth2Protocol::OAuth2Protocol_ClientAssertionType => ClientAssertionAuthenticationContext::RegisteredAssertionType,
            OAuth2Protocol::OAuth2Protocol_ClientAssertion     => $jws->toCompactSerialization()
        );


        $response = $this->action
        (
            "POST",
            "OAuth2ProviderController@token",
            $params,
            array(),
            array(),
            array()
        );

        $this->assertResponseStatus(200);

        $content = $response->getContent();

        $response = json_decode($content);
        $access_token = $response->access_token;
        $id_token     = $response->id_token;

        $this->assertTrue(!empty($access_token));
        $this->assertTrue(!empty($id_token));
    }

    public function testImplicitFlow()
    {
        // use a public client

        //already given consent

        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwKlfSyQ3x.openstack.client';

        $params = array
        (
            'client_id'    => $client_id,
            'redirect_uri'  => 'https://www.test.com/oauth2',
            'response_type' => OAuth2Protocol::OAuth2Protocol_ResponseType_IdToken . OAuth2Protocol::OAuth2Protocol_ResponseType_Delimiter . OAuth2Protocol::OAuth2Protocol_ResponseType_Token,
            'scope'         => sprintf('%s profile email %s', OAuth2Protocol::OpenIdConnect_Scope, OAuth2Protocol::OfflineAccess_Scope),
            OAuth2Protocol::OAuth2Protocol_LoginHint => 'sebastian@tipit.net',
            OAuth2Protocol::OAuth2Protocol_Prompt => OAuth2Protocol::OAuth2Protocol_Prompt_Consent,
            OAuth2Protocol::OAuth2Protocol_MaxAge => 3200
        );

        $response = $this->action("POST", "OAuth2ProviderController@authorize",
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


        $response = $this->action('POST', 'UserController@getConsent', array(
            'trust'  => 'AllowOnce',
            '_token' => Session::token()
        ));

        $this->assertResponseStatus(302);

        // get response

        $response = $this->action("GET", "OAuth2ProviderController@authorize",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $url      = $response->getTargetUrl();
        $comps    = @parse_url($url);
        $fragment = $comps['fragment'];

        $this->assertTrue(!empty($fragment));
        $output = array();
        parse_str($fragment, $output);

        $this->assertTrue(array_key_exists('access_token', $output) );
        $this->assertTrue(!empty($output['access_token']) );
        $this->assertTrue(array_key_exists('id_token', $output) );
        $this->assertTrue(!empty($output['id_token']) );

    }

}