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
use Auth\User;
use OpenId\Extensions\Implementations\OpenIdOAuth2Extension;
use OpenId\Extensions\Implementations\OpenIdSREGExtension;
use OpenId\Helpers\OpenIdCryptoHelper;
use OpenId\OpenIdProtocol;
use Utils\Services\IAuthService;
use Zend\Crypt\PublicKey\DiffieHellman;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Models\OpenId\OpenIdTrustedSite;
use OpenId\Extensions\Implementations\OpenIdSREGExtension_1_0;
/**
 * Class OpenIdProtocolTest
 * Test Suite for OpenId Protocol
 */
final class OpenIdProtocolTest extends OpenStackIDBaseTest
{
    private $current_realm;
    private $g;
    private $private;
    private $public;
    private $mod;
    private $oauth2_client_id;
    private $oauth2_client_secret;
    private $user;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        //DH openid values
        $this->g = '1';
        $this->private = '84009535308644335779530519631942543663544485189066558731295758689838227409144125540638118058012144795574289866857191302071807568041343083679600155026066530597177004145874642611724010339353151653679189142289183802715816551715563883085859667759854344959305451172754264893136955464706052993052626766687910313992';
        $this->public = '93500922748114712465435925279613158240858799671601934136793652488458659380414896628304484614933937038790006320444306607890979422427297815641372302594684991758687126229761033142429422299990743006497200988301031430937819368909849994628108111270360657896230712920491471398605159969300956278883668998797148755353';
        $this->mod = '155172898181473697471232257763715539915724801966915404479707795314057629378541917580651227423698188993727816152646631438561595825688188889951272158842675419950341258706556549803580104870537681476726513255747040765857479291291572334510643245094715007229621094194349783925984760375594985848253359305585439638443';
        $this->oauth2_client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $this->oauth2_client_secret = 'ITc/6Y5N7kOtGKhg';
    }

    protected function prepareForTests()
    {
        parent::prepareForTests();
        $this->current_realm = Config::get('app.url');
        $this->user          = User::where('identifier', '=', 'sebastian.marcet')->first();
        $this->be($this->user);
        Session::start();
    }

    /**
     * parse openid response from an url
     * @param $url
     * @return array
     */
    private function parseOpenIdResponse($url)
    {
        $url_parts = @parse_url($url);
        $openid_response = array();
        $query_params = explode('&', $url_parts['query']);
        foreach ($query_params as $param) {
            $aux = explode('=', $param, 2);
            $openid_response[$aux[0]] = @urldecode($aux[1]);
        }

        return $openid_response;
    }

    private function getOpenIdResponseLineBreak($content)
    {
        $params = explode("\n", $content);
        $res = array();
        foreach ($params as $param) {
            if (empty($param)) {
                continue;
            }
            $openid_param = explode(':', $param, 2);
            $res[$openid_param[0]] = $openid_param[1];
        }

        return $res;
    }

    /**
     * Exact copies of all fields from the authentication response, except for "openid.mode".
     * @param $openid_response
     * @return array
     */
    private function prepareCheckAuthenticationParams($openid_response)
    {
        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::CheckAuthenticationMode,
        );

        $encode = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo) => OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo),
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId),
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint) => OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint),
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm) => OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm),
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity) => OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity),
        );

        foreach ($openid_response as $key => $value) {
            if (!array_key_exists($key, $params)) {
                $params[$key] = array_key_exists($key, $encode) ? @urldecode($value) : $value;
            }
        }

        return $params;
    }

    // test for session associations

    public function testAssociationSessionRequestDiffieHellmanSha1()
    {

        $b64_public = base64_encode(OpenIdCryptoHelper::convert($this->public, DiffieHellman::FORMAT_NUMBER,
            DiffieHellman::FORMAT_BTWOC));

        $this->assertTrue($b64_public === 'AIUmVPMheb/hEupD5m6veEEstnBVteyZPy+mlYX7ygxygLG/XuHFa8q4lZERJ9u1DNFOpXHRDq5RbjsaUYRDOtyrbkGbeKo5tPqjsynjXtoMAItxkxCU4jpQLvH85P+u7DeA0h3kKNHFa90ijZTIGSSDRF5wW9N+QPCUCt4G4xWZ');

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::AssociateMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocType) => OpenIdProtocol::SignatureAlgorithmHMAC_SHA1,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_SessionType) => OpenIdProtocol::AssociationSessionTypeDHSHA1,
            OpenIdProtocol::param(OpenIdProtocol::OpenIdProtocol_DHConsumerPublic) => $b64_public,
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(200);

        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());

        $this->assertTrue(isset($openid_response['ns']));
        $this->assertTrue($openid_response['ns'] === OpenIdProtocol::OpenID2MessageType);
        $this->assertTrue(isset($openid_response['assoc_type']));
        $this->assertTrue($openid_response['assoc_type'] === OpenIdProtocol::SignatureAlgorithmHMAC_SHA1);
        $this->assertTrue(isset($openid_response['session_type']));
        $this->assertTrue($openid_response['session_type'] === OpenIdProtocol::AssociationSessionTypeDHSHA1);
        $this->assertTrue(isset($openid_response['assoc_handle']));
        $this->assertTrue(isset($openid_response['dh_server_public']));
        $this->assertTrue(isset($openid_response['enc_mac_key']));
        $this->assertTrue(isset($openid_response['expires_in']));

    }

    public function testAssociationSessionRequestDiffieHellmanSha256()
    {

        $b64_public = base64_encode(OpenIdCryptoHelper::convert($this->public, DiffieHellman::FORMAT_NUMBER,
            DiffieHellman::FORMAT_BTWOC));

        $this->assertTrue($b64_public === 'AIUmVPMheb/hEupD5m6veEEstnBVteyZPy+mlYX7ygxygLG/XuHFa8q4lZERJ9u1DNFOpXHRDq5RbjsaUYRDOtyrbkGbeKo5tPqjsynjXtoMAItxkxCU4jpQLvH85P+u7DeA0h3kKNHFa90ijZTIGSSDRF5wW9N+QPCUCt4G4xWZ');

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::AssociateMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocType) => OpenIdProtocol::SignatureAlgorithmHMAC_SHA256,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_SessionType) => OpenIdProtocol::AssociationSessionTypeDHSHA256,
            OpenIdProtocol::param(OpenIdProtocol::OpenIdProtocol_DHConsumerPublic) => $b64_public,
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(200);

        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());

        $this->assertTrue(isset($openid_response['ns']));
        $this->assertTrue($openid_response['ns'] === OpenIdProtocol::OpenID2MessageType);
        $this->assertTrue(isset($openid_response['assoc_type']));
        $this->assertTrue($openid_response['assoc_type'] === OpenIdProtocol::SignatureAlgorithmHMAC_SHA256);
        $this->assertTrue(isset($openid_response['session_type']));
        $this->assertTrue($openid_response['session_type'] === OpenIdProtocol::AssociationSessionTypeDHSHA256);
        $this->assertTrue(isset($openid_response['assoc_handle']));
        $this->assertTrue(isset($openid_response['dh_server_public']));
        $this->assertTrue(isset($openid_response['enc_mac_key']));
        $this->assertTrue(isset($openid_response['expires_in']));

    }

    public function testAssociationSessionRequestNoEncryption()
    {

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::AssociateMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocType) => OpenIdProtocol::AssociationSessionTypeNoEncryption,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_SessionType) => OpenIdProtocol::AssociationSessionTypeNoEncryption,
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(200);

        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());

        $this->assertTrue(isset($openid_response['ns']));
        $this->assertTrue($openid_response['ns'] === OpenIdProtocol::OpenID2MessageType);
        $this->assertTrue(isset($openid_response['assoc_type']));
        $this->assertTrue($openid_response['assoc_type'] === OpenIdProtocol::AssociationSessionTypeNoEncryption);
        $this->assertTrue(isset($openid_response['session_type']));
        $this->assertTrue($openid_response['session_type'] === OpenIdProtocol::AssociationSessionTypeNoEncryption);
        $this->assertTrue(isset($openid_response['assoc_handle']) && !empty($openid_response['assoc_handle']));
        $this->assertTrue(isset($openid_response['expires_in']));
        $this->assertTrue(isset($openid_response['mac_key']) && !empty($openid_response['mac_key']));

    }

    // test for authentication

    public function testAuthenticationSetupModePrivateAssociation()
    {
        //set login info

         $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)     => "https://www.newsite.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)  => "https://www.newsite.com/return_to/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)  => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();

        // post consent response ...

        $consent_response = $this->call('POST', $url, array
            (
                'trust'  => array('AllowOnce'),
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $auth_response = $this->action("GET", "OpenId\OpenIdProviderController@endpoint",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $openid_response = $this->parseOpenIdResponse($auth_response->getTargetUrl());

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));

        //http://openid.net/specs/openid-authentication-2_0.html#check_auth
        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $this->prepareCheckAuthenticationParams($openid_response));
        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());
        $this->assertResponseStatus(200);
        $this->assertTrue($openid_response['is_valid'] === 'true');
    }

    public function testInvalidTLD_Wilcard_COM_UK()
    {
        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)     => "https://*.com.uk",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)  => "https://devbranch.openstack.com.uk/return_to.php",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)  => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $url        = $response->getTargetUrl();
        $parsed_url = @parse_url($url);
        $query      = isset($parsed_url['query']) ?  $parsed_url['query']: null;
        $this->assertTrue(!empty($query));
        parse_str($query, $query_array);
        $this->assertTrue(isset($query_array['openid_error']));
        $error = $query_array['openid_error'];
        $this->assertTrue(str_contains($error,"Invalid OpenId Message : realm is not valid" ));
    }

    public function testInvalidTLD_Wilcard_UK()
    {
        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)     => "https://*.uk",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)  => "https://devbranch.openstack.com.uk/return_to.php",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)  => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $url        = $response->getTargetUrl();
        $parsed_url = @parse_url($url);
        $query      = isset($parsed_url['query']) ?  $parsed_url['query']: null;
        $this->assertTrue(!empty($query));
        parse_str($query, $query_array);
        $this->assertTrue(isset($query_array['openid_error']));
        $error = $query_array['openid_error'];
        $this->assertTrue(str_contains($error,"Invalid OpenId Message : realm is not valid" ));
    }

    public function testValidTLD_Wilcard()
    {
        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)     => "https://*.openstack.org",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)  => "https://devbranch.openstack.org/return_to.php",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)  => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $url        = $response->getTargetUrl();
        $parsed_url = @parse_url($url);
        $this->assertTrue(!isset($parsed_url['query']));
        $this->assertTrue(str_contains($url, "https://local.openstackid.openstack.org/accounts/user/consent"));
    }

    public function testValidTLD_SameDomain()
    {
        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)     => "https://devbranch.openstack.org",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)  => "https://devbranch.openstack.org/return_to.php",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)  => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $url        = $response->getTargetUrl();
        $parsed_url = @parse_url($url);
        $this->assertTrue(!isset($parsed_url['query']));
        $this->assertTrue(str_contains($url, "https://local.openstackid.openstack.org/accounts/user/consent"));
    }

    public function testValidTLD_SingleLevelDomain()
    {
        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)     => "https://myrefstack",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)  => "https://myrefstack/return_to.php",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)  => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $url        = $response->getTargetUrl();
        $parsed_url = @parse_url($url);
        $this->assertTrue(!isset($parsed_url['query']));
        $this->assertTrue(str_contains($url, "https://local.openstackid.openstack.org/accounts/user/consent"));
    }

    public function testValidTLD_IPDomain()
    {
        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)     => "https://192.0.0.1",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)  => "https://192.0.0.1/return_to.php",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)  => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $url        = $response->getTargetUrl();
        $parsed_url = @parse_url($url);
        $this->assertTrue(!isset($parsed_url['query']));
        $this->assertTrue(str_contains($url, "https://local.openstackid.openstack.org/accounts/user/consent"));
    }

    public function testAuthenticationSetupModeSessionAssociationDHSha256()
    {

        $b64_public = base64_encode(OpenIdCryptoHelper::convert($this->public, DiffieHellman::FORMAT_NUMBER,
            DiffieHellman::FORMAT_BTWOC));

        $this->assertTrue($b64_public === 'AIUmVPMheb/hEupD5m6veEEstnBVteyZPy+mlYX7ygxygLG/XuHFa8q4lZERJ9u1DNFOpXHRDq5RbjsaUYRDOtyrbkGbeKo5tPqjsynjXtoMAItxkxCU4jpQLvH85P+u7DeA0h3kKNHFa90ijZTIGSSDRF5wW9N+QPCUCt4G4xWZ');

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::AssociateMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocType) => OpenIdProtocol::SignatureAlgorithmHMAC_SHA256,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_SessionType) => OpenIdProtocol::AssociationSessionTypeDHSHA256,
            OpenIdProtocol::param(OpenIdProtocol::OpenIdProtocol_DHConsumerPublic) => $b64_public,
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(200);

        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());

        $this->assertTrue(isset($openid_response['ns']));
        $this->assertTrue($openid_response['ns'] === OpenIdProtocol::OpenID2MessageType);
        $this->assertTrue(isset($openid_response['assoc_type']));
        $this->assertTrue($openid_response['assoc_type'] === OpenIdProtocol::SignatureAlgorithmHMAC_SHA256);
        $this->assertTrue(isset($openid_response['session_type']));
        $this->assertTrue($openid_response['session_type'] === OpenIdProtocol::AssociationSessionTypeDHSHA256);
        $this->assertTrue(isset($openid_response['assoc_handle']));
        $this->assertTrue(isset($openid_response['dh_server_public']));
        $this->assertTrue(isset($openid_response['enc_mac_key']));
        $this->assertTrue(isset($openid_response['expires_in']));

        Session::put("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm) => "https://www.test.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo) => "https://www.test.com/oauth2",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity) => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocHandle) => $openid_response['assoc_handle'],
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $openid_response = $this->parseOpenIdResponse($response->getTargetUrl());

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)]));
    }

    public function testAuthenticationSetupModeSessionAssociationDHSha256InvalidParams()
    {

        $b64_public = base64_encode(OpenIdCryptoHelper::convert($this->public, DiffieHellman::FORMAT_NUMBER,
            DiffieHellman::FORMAT_BTWOC));

        $this->assertTrue($b64_public === 'AIUmVPMheb/hEupD5m6veEEstnBVteyZPy+mlYX7ygxygLG/XuHFa8q4lZERJ9u1DNFOpXHRDq5RbjsaUYRDOtyrbkGbeKo5tPqjsynjXtoMAItxkxCU4jpQLvH85P+u7DeA0h3kKNHFa90ijZTIGSSDRF5wW9N+QPCUCt4G4xWZ');

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::AssociateMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocType) => OpenIdProtocol::SignatureAlgorithmHMAC_SHA256,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_SessionType) => OpenIdProtocol::AssociationSessionTypeDHSHA256,
            OpenIdProtocol::param(OpenIdProtocol::OpenIdProtocol_DHGen) => base64_encode(OpenIdCryptoHelper::convert(1, DiffieHellman::FORMAT_NUMBER, DiffieHellman::FORMAT_BTWOC)),
            OpenIdProtocol::param(OpenIdProtocol::OpenIdProtocol_DHModulus) => base64_encode(OpenIdCryptoHelper::convert(PHP_INT_MAX, DiffieHellman::FORMAT_NUMBER, DiffieHellman::FORMAT_BTWOC)),
            OpenIdProtocol::param(OpenIdProtocol::OpenIdProtocol_DHConsumerPublic) => $b64_public,
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(400);

    }

    public function testAuthenticationSetupModeSessionAssociationDHSha1InvalidParamsFromWWWSite()
    {

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::AssociateMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocType) => OpenIdProtocol::SignatureAlgorithmHMAC_SHA1,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_SessionType) => OpenIdProtocol::AssociationSessionTypeDHSHA1,
            OpenIdProtocol::param(OpenIdProtocol::OpenIdProtocol_DHGen) => 'AQ==',
            OpenIdProtocol::param(OpenIdProtocol::OpenIdProtocol_DHModulus) => 'AQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQE=',
            OpenIdProtocol::param(OpenIdProtocol::OpenIdProtocol_DHConsumerPublic) => 'AQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQE=',
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(400);
    }

    public function testAuthenticationInvalidParamsFromWWWSite()
    {

        $params = array(
            'openid_ns' => 'http://specs.openid.net/auth/2.0',
            'openid_ns_sreg' => 'http://openid.net/extensions/sreg/1.1',
            'openid_sreg_required' => 'email,fullname',
            'openid_sreg_optional' => 'country,language',
            'openid_realm' => '../index.html',
            'openid_mode' => 'checkid_setup',
            'openid_return_to' => 'badlogin.html?url=/OpenStackIdAuthenticator&BackURL=%2Fsummit%2Faustin-2016%2Fcall-for-speakers%2Fshow%2F8234',
            'openid_identity' => 'http://specs.openid.net/auth/2.0/identifier_select',
            'openid_claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(404);
    }

    public function testAuthenticationCheckImmediateAuthenticationPrivateSession()
    {
        //set login info
        Session::put("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        //add trusted site
        $site = new OpenIdTrustedSite;
        $site->realm = 'https://www.test.com/';
        $site->policy = IAuthService::AuthorizationResponse_AllowForever;
        $site->user_id = $this->user->getId();
        $site->data = json_encode(array());
        $site->Save();

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::ImmediateMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm) => "https://www.test.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo) => "https://www.test.com/oauth2",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity) => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $openid_response = $this->parseOpenIdResponse($response->getTargetUrl());

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)]));
    }


    /**
     */
    public function testAuthenticationCheckImmediateAuthenticationPrivateSession_SetupNeeded()
    {
        //set login info
        Session::put("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);
        $this->user->trusted_sites()->delete();
        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::ImmediateMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm) => "https://www.test.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo) => "https://www.test.com/oauth2",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity) => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $openid_response = $this->parseOpenIdResponse($response->getTargetUrl());

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));
        $this->assertTrue($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)] == OpenIdProtocol::SetupNeededMode);

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));
        $this->assertTrue($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)] == 'https://www.test.com/oauth2');
    }


    //extension tests

    public function testCheckSetupSREGExtension1_0()
    {

        //set login info
        Session::put("openid.authorization.response", IAuthService::AuthorizationResponse_AllowForever);
        $sreg_required_params = array('email', 'fullname', 'nickname');

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm) => "https://www.test.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo) => "https://www.test.com/oauth2",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity) => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
            //sreg
            OpenIdSREGExtension::paramNamespace() => OpenIdSREGExtension_1_0::NamespaceUrl,
            OpenIdSREGExtension::param(OpenIdSREGExtension::Required) => implode(",", $sreg_required_params),

        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $openid_response = $this->parseOpenIdResponse($response->getTargetUrl());

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)]));

        //sreg

        $this->assertTrue(isset($openid_response[OpenIdSREGExtension::paramNamespace()]));
        $this->assertTrue($openid_response[OpenIdSREGExtension::paramNamespace()] === OpenIdSREGExtension_1_0::NamespaceUrl);

        $this->assertTrue(isset($openid_response[OpenIdSREGExtension::param(OpenIdSREGExtension::FullName)]));
        $full_name = $openid_response[OpenIdSREGExtension::param(OpenIdSREGExtension::FullName)];
        $this->assertTrue(!empty($full_name) && $full_name === 'Sebastian Marcet');

        $this->assertTrue(isset($openid_response[OpenIdSREGExtension::param(OpenIdSREGExtension::Email)]));
        $email = $openid_response[OpenIdSREGExtension::param(OpenIdSREGExtension::Email)];
        $this->assertTrue(!empty($email) && $email === 'sebastian@tipit.net');

        //http://openid.net/specs/openid-authentication-2_0.html#check_auth
        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint",
            $this->prepareCheckAuthenticationParams($openid_response));
        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());
        $this->assertResponseStatus(200);
        $this->assertTrue($openid_response['is_valid'] === 'true');
    }

    public function testCheckSetupSREGExtension1_1()
    {

        //set login info
        Session::put("openid.authorization.response", IAuthService::AuthorizationResponse_AllowForever);
        $sreg_required_params = array('email', 'fullname');

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm) => "https://www.test.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo) => "https://www.test.com/oauth2",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity) => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
            //sreg
            OpenIdSREGExtension::paramNamespace() => OpenIdSREGExtension::NamespaceUrl,
            OpenIdSREGExtension::param(OpenIdSREGExtension::Required) => implode(",", $sreg_required_params),

        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $openid_response = $this->parseOpenIdResponse($response->getTargetUrl());

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)]));

        //sreg

        $this->assertTrue(isset($openid_response[OpenIdSREGExtension::paramNamespace()]));
        $this->assertTrue($openid_response[OpenIdSREGExtension::paramNamespace()] === OpenIdSREGExtension::NamespaceUrl);

        $this->assertTrue(isset($openid_response[OpenIdSREGExtension::param(OpenIdSREGExtension::FullName)]));
        $full_name = $openid_response[OpenIdSREGExtension::param(OpenIdSREGExtension::FullName)];
        $this->assertTrue(!empty($full_name) && $full_name === 'Sebastian Marcet');

        $this->assertTrue(isset($openid_response[OpenIdSREGExtension::param(OpenIdSREGExtension::Email)]));
        $email = $openid_response[OpenIdSREGExtension::param(OpenIdSREGExtension::Email)];
        $this->assertTrue(!empty($email) && $email === 'sebastian@tipit.net');

        //http://openid.net/specs/openid-authentication-2_0.html#check_auth
        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint",
            $this->prepareCheckAuthenticationParams($openid_response));
        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());
        $this->assertResponseStatus(200);
        $this->assertTrue($openid_response['is_valid'] === 'true');
    }

    public function testCheckSetupSREGExtensionNotRequired()
    {

        //set login info
        Session::put("openid.authorization.response", IAuthService::AuthorizationResponse_AllowForever);
        $sreg_required_params = array('email', 'fullname');

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm) => "https://www.test.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo) => "https://www.test.com/oauth2",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity) => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
            //sreg
            OpenIdSREGExtension::paramNamespace() => OpenIdSREGExtension::NamespaceUrl,
            OpenIdSREGExtension::param(OpenIdSREGExtension::Optional) => implode(",", $sreg_required_params),

        );

        $response = $this->action("POST", "OpenId\\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $openid_response = $this->parseOpenIdResponse($response->getTargetUrl());

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)]));

        //sreg

        $this->assertTrue(isset($openid_response[OpenIdSREGExtension::paramNamespace()]));
        $this->assertTrue($openid_response[OpenIdSREGExtension::paramNamespace()] === OpenIdSREGExtension::NamespaceUrl);

        $this->assertTrue(isset($openid_response[OpenIdSREGExtension::param(OpenIdSREGExtension::FullName)]));
        $full_name = $openid_response[OpenIdSREGExtension::param(OpenIdSREGExtension::FullName)];
        $this->assertTrue(!empty($full_name) && $full_name === 'Sebastian Marcet');

        $this->assertTrue(isset($openid_response[OpenIdSREGExtension::param(OpenIdSREGExtension::Email)]));
        $email = $openid_response[OpenIdSREGExtension::param(OpenIdSREGExtension::Email)];
        $this->assertTrue(!empty($email) && $email === 'sebastian@tipit.net');

        //http://openid.net/specs/openid-authentication-2_0.html#check_auth
        $response = $this->action(
            "POST",
            "OpenId\\OpenIdProviderController@endpoint",
            $this->prepareCheckAuthenticationParams($openid_response)
        );

        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());
        $this->assertResponseStatus(200);
        $this->assertTrue($openid_response['is_valid'] === 'true');
    }

    /**
     * test openid oauth2 extension
     * https://developers.google.com/accounts/docs/OpenID#oauth
     */

    public function testCheckSetupOAuth2Extension()
    {

        $sreg_required_params = array('email', 'fullname');

        $scope = array(
            sprintf('%s/resource-server/read', $this->current_realm),
            sprintf('%s/resource-server/read.page', $this->current_realm),
            sprintf('%s/resource-server/write', $this->current_realm),
            sprintf('%s/resource-server/delete', $this->current_realm),
            sprintf('%s/resource-server/update', $this->current_realm),
            sprintf('%s/resource-server/update.status', $this->current_realm),
            sprintf('%s/resource-server/regenerate.secret', $this->current_realm),
        );

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm) => "https://www.test.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo) => "https://www.test.com/oauth2",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity) => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
            //oauth2
            OpenIdOAuth2Extension::paramNamespace() => OpenIdOAuth2Extension::NamespaceUrl,
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::ClientId) => $this->oauth2_client_id,
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::Scope) => implode(' ', $scope),
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::State) => uniqid(),
            //sreg
            OpenIdSREGExtension::paramNamespace() => OpenIdSREGExtension::NamespaceUrl,
            OpenIdSREGExtension::param(OpenIdSREGExtension::Required) => implode(",", $sreg_required_params),
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();

        $response = $this->call('GET', $url);

        $this->assertResponseStatus(200);

        $consent_html_content = $response->getContent();

        $this->assertTrue(str_contains($consent_html_content, 'Welcome to OpenStackId - consent'));
        $this->assertTrue(str_contains($consent_html_content, 'The site has also requested some personal information'));
        $this->assertTrue(str_contains($consent_html_content, 'The site has also requested some permissions for following OAuth2 application'));


        $response = $this->call('POST', $url, array(
            'trust'  => array('AllowOnce'),
            '_token' => Session::token()
        ));

        $response = $this->call('GET', $response->getTargetUrl());

        $openid_response = $this->parseOpenIdResponse($response->getTargetUrl());

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)]));

        //oauth2

        $this->assertTrue(isset($openid_response[OpenIdOAuth2Extension::paramNamespace()]));
        $this->assertTrue($openid_response[OpenIdOAuth2Extension::paramNamespace()] === OpenIdOAuth2Extension::NamespaceUrl);

        $this->assertTrue(isset($openid_response[OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::RequestToken)]));
        $this->assertTrue(!empty($openid_response[OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::RequestToken)]));

        $this->assertTrue(isset($openid_response[OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::Scope)]));
        $this->assertTrue(!empty($openid_response[OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::Scope)]));

        //sreg

        $this->assertTrue(isset($openid_response[OpenIdSREGExtension::paramNamespace()]));
        $this->assertTrue($openid_response[OpenIdSREGExtension::paramNamespace()] === OpenIdSREGExtension::NamespaceUrl);

        $this->assertTrue(isset($openid_response[OpenIdSREGExtension::param(OpenIdSREGExtension::FullName)]));
        $full_name = $openid_response[OpenIdSREGExtension::param(OpenIdSREGExtension::FullName)];
        $this->assertTrue(!empty($full_name) && $full_name === 'Sebastian Marcet');

        $this->assertTrue(isset($openid_response[OpenIdSREGExtension::param(OpenIdSREGExtension::Email)]));
        $email = $openid_response[OpenIdSREGExtension::param(OpenIdSREGExtension::Email)];
        $this->assertTrue(!empty($email) && $email === 'sebastian@tipit.net');

        //http://openid.net/specs/openid-authentication-2_0.html#check_auth
        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint",
            $this->prepareCheckAuthenticationParams($openid_response));
        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());
        $this->assertResponseStatus(200);
        $this->assertTrue($openid_response['is_valid'] === 'true');
    }

    public function testCheckSetupOAuth2ExtensionWrongClientId()
    {

        //set login info
        Session::put("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        $scope = array(
            sprintf('%s/resource-server/read', $this->current_realm),
            sprintf('%s/resource-server/read.page', $this->current_realm),
            sprintf('%s/resource-server/write', $this->current_realm),
            sprintf('%s/resource-server/delete', $this->current_realm),
            sprintf('%s/resource-server/update', $this->current_realm),
            sprintf('%s/resource-server/update.status', $this->current_realm),
            sprintf('%s/resource-server/regenerate.secret', $this->current_realm),
        );

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm) => "https://www.test.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo) => "https://www.test.com/oauth2",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity) => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
            //oauth2
            OpenIdOAuth2Extension::paramNamespace() => OpenIdOAuth2Extension::NamespaceUrl,
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::ClientId) => 'wrong_client_id',
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::Scope) => implode(' ', $scope),
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::State) => uniqid(),
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $openid_response = $this->parseOpenIdResponse($response->getTargetUrl());

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)]));

        //oauth 2

        $this->assertTrue(isset($openid_response[OpenIdOAuth2Extension::paramNamespace()]));
        $this->assertTrue($openid_response[OpenIdOAuth2Extension::paramNamespace()] === OpenIdOAuth2Extension::NamespaceUrl);

        $this->assertTrue(!isset($openid_response[OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::Scope)]));

        $this->assertTrue(!isset($openid_response[OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::RequestToken)]));


        //http://openid.net/specs/openid-authentication-2_0.html#check_auth
        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint",
            $this->prepareCheckAuthenticationParams($openid_response));
        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());
        $this->assertResponseStatus(200);
        $this->assertTrue($openid_response['is_valid'] === 'true');
    }

    public function testCheckSetupOAuth2ExtensionBadRequest()
    {

        //set login info
        Session::put("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        $scope = array(
            sprintf('%s/resource-server/read', $this->current_realm),
            sprintf('%s/resource-server/read.page', $this->current_realm),
            sprintf('%s/resource-server/write', $this->current_realm),
            sprintf('%s/resource-server/delete', $this->current_realm),
            sprintf('%s/resource-server/update', $this->current_realm),
            sprintf('%s/resource-server/update.status', $this->current_realm),
            sprintf('%s/resource-server/regenerate.secret', $this->current_realm),
        );

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm) => "https://www.test.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo) => "https://www.test.com/oauth2",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity) => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
            //oauth2
            OpenIdOAuth2Extension::paramNamespace() => OpenIdOAuth2Extension::NamespaceUrl,
            //missing client id
            //OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::ClientId)   => 'wrong_client_id',
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::Scope) => implode(' ', $scope),
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::State) => uniqid(),
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $openid_response = $this->parseOpenIdResponse($response->getTargetUrl());

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)]));

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)]));
        $this->assertTrue(!empty($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)]));

        //oauth 2

        $this->assertTrue(isset($openid_response[OpenIdOAuth2Extension::paramNamespace()]));
        $this->assertTrue($openid_response[OpenIdOAuth2Extension::paramNamespace()] === OpenIdOAuth2Extension::NamespaceUrl);

        $this->assertTrue(!isset($openid_response[OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::Scope)]));

        $this->assertTrue(!isset($openid_response[OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::RequestToken)]));


        //http://openid.net/specs/openid-authentication-2_0.html#check_auth
        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint",
            $this->prepareCheckAuthenticationParams($openid_response));
        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());
        $this->assertResponseStatus(200);
        $this->assertTrue($openid_response['is_valid'] === 'true');
    }

    public function testCheckSetupOAuth2ExtensionSubView()
    {

        //set login info
        $user = User::where('identifier', '=', 'sebastian.marcet')->first();
        Auth::login($user);

        $scope = array(
            sprintf('%s/resource-server/read', $this->current_realm),
            sprintf('%s/resource-server/read.page', $this->current_realm),
            sprintf('%s/resource-server/write', $this->current_realm),
            sprintf('%s/resource-server/delete', $this->current_realm),
            sprintf('%s/resource-server/update', $this->current_realm),
            sprintf('%s/resource-server/update.status', $this->current_realm),
            sprintf('%s/resource-server/regenerate.secret', $this->current_realm),
        );

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm) => "https://www.test.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo) => "https://www.test.com/oauth2",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity) => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
            //oauth2
            OpenIdOAuth2Extension::paramNamespace() => OpenIdOAuth2Extension::NamespaceUrl,
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::ClientId) => $this->oauth2_client_id,
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::Scope) => implode(' ', $scope),
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::State) => uniqid(),
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $content = $response->getContent();
    }

    public function testDiscovery()
    {
        $response = $this->action("GET", "HomeController@index",
            array(),
            array(),
            array(),
            array(),
            // Symfony interally prefixes headers with "HTTP", so
            array('HTTP_Accept' => 'text/html; q=0.3, application/xhtml+xml; q=0.5, application/xrds+xml'));
        $this->assertResponseStatus(200);
        // I just needed to access the public
        // headers var (which is a Symfony ResponseHeaderBag object)
        $this->assertEquals('application/xrds+xml; charset=UTF-8', $response->headers->get('Content-Type'));

        $content = $response->getContent();

        $this->assertTrue(strpos($content, '<xrds:XRDS') !== false);
        $this->assertTrue(strpos($content, 'http://specs.openid.net/auth/2.0/server') !== false);
    }

    public function testInvalidRequestK()
    {
        $params = [];

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(400);

    }

    public function testInvalidAssociation()
    {
        //set login info

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)     => "https://www.newsite.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)  => "https://www.newsite.com/return_to/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)  => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
        );

        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);

        $this->assertResponseStatus(302);

        $url = $response->getTargetUrl();

        // post consent response ...

        $consent_response = $this->call('POST', $url, array
            (
                'trust'  => array('AllowOnce'),
                '_token' => Session::token()
            )
        );

        $this->assertResponseStatus(302);

        $auth_response = $this->action("GET", "OpenId\OpenIdProviderController@endpoint",
            array(),
            array(),
            array(),
            array());

        $this->assertResponseStatus(302);

        $openid_response = $this->parseOpenIdResponse($auth_response->getTargetUrl());

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));

        //http://openid.net/specs/openid-authentication-2_0.html#check_auth
        $params   = $this->prepareCheckAuthenticationParams($openid_response);
        $params['openid.assoc_handle'] = "FAKE";
        $response = $this->action("POST", "OpenId\OpenIdProviderController@endpoint", $params);
        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());
        $this->assertResponseStatus(400);
    }
}
