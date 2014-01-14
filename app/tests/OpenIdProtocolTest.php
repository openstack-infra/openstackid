<?php

use openid\OpenIdProtocol;
use auth\OpenIdUser;
use oauth2\OAuth2Protocol;
use utils\services\IAuthService;
use openid\extensions\implementations\OpenIdOAuth2Extension;
use openid\helpers\OpenIdCryptoHelper;
use Zend\Crypt\PublicKey\DiffieHellman;

/**
 * Class OpenIdProtocolTest
 * Test Suite for OpenId Protocol
 */
class OpenIdProtocolTest extends TestCase
{
    private $current_realm;
    private $g;
    private $private;
    private $public;
    private $mod;
    private $oauth2_client_id;
    private $oauth2_client_secret;

    public function __construct(){
        //DH openid values
        $this->g                    = '2';
        $this->private              = '84009535308644335779530519631942543663544485189066558731295758689838227409144125540638118058012144795574289866857191302071807568041343083679600155026066530597177004145874642611724010339353151653679189142289183802715816551715563883085859667759854344959305451172754264893136955464706052993052626766687910313992';
        $this->public               = '93500922748114712465435925279613158240858799671601934136793652488458659380414896628304484614933937038790006320444306607890979422427297815641372302594684991758687126229761033142429422299990743006497200988301031430937819368909849994628108111270360657896230712920491471398605159969300956278883668998797148755353';
        $this->mod                  = '155172898181473697471232257763715539915724801966915404479707795314057629378541917580651227423698188993727816152646631438561595825688188889951272158842675419950341258706556549803580104870537681476726513255747040765857479291291572334510643245094715007229621094194349783925984760375594985848253359305585439638443';
        $this->oauth2_client_id     = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $this->oauth2_client_secret = 'ITc/6Y5N7kOtGKhg';
    }

    protected function prepareForTests()
    {
        parent::prepareForTests();
        Route::enableFilters();
        $this->current_realm = Config::get('app.url');
    }

    /**
     * parse openid response from an url
     * @param $url
     * @return array
     */
    private function parseOpenIdResponse($url){
        $url_parts = @parse_url($url);
        $openid_response = array();
        $query_params = explode('&', $url_parts['query']);
        foreach ($query_params as $param) {
            $aux = explode('=', $param, 2);
            $openid_response[$aux[0]] = @urldecode($aux[1]);
        }
        return $openid_response;
    }

    private function getOpenIdResponseLineBreak($content){
        $params = explode("\n",$content);
        $res = array();
        foreach($params as $param){
            if(empty($param)) continue;
            $openid_param = explode(':',$param,2);
            $res[$openid_param[0]] = $openid_param[1];
        }
        return $res;
    }

    /**
     * Exact copies of all fields from the authentication response, except for "openid.mode".
     * @param $openid_response
     * @return array
     */
    private function prepareCheckAuthenticationParams($openid_response){
        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)           => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)         => OpenIdProtocol::CheckAuthenticationMode,
        );

        $encode = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo) =>OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo),
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) =>OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId),
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint) =>OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint),
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm) =>OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm),
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity) =>OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity),
        );

        foreach($openid_response as $key => $value){
            if(!array_key_exists($key,$params))
                $params[$key] = array_key_exists($key,$encode)? @urldecode($value):$value;
        }
        return $params;
    }

    // test for session associations

    public function testAssociationSessionRequestDiffieHellmanSha1(){

        $b64_public = base64_encode(OpenIdCryptoHelper::convert($this->public,DiffieHellman::FORMAT_NUMBER,DiffieHellman::FORMAT_BTWOC));

        $this->assertTrue($b64_public === 'AIUmVPMheb/hEupD5m6veEEstnBVteyZPy+mlYX7ygxygLG/XuHFa8q4lZERJ9u1DNFOpXHRDq5RbjsaUYRDOtyrbkGbeKo5tPqjsynjXtoMAItxkxCU4jpQLvH85P+u7DeA0h3kKNHFa90ijZTIGSSDRF5wW9N+QPCUCt4G4xWZ');

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::AssociateMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocType) => OpenIdProtocol::SignatureAlgorithmHMAC_SHA1,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_SessionType) => OpenIdProtocol::AssociationSessionTypeDHSHA1,
            OpenIdProtocol::param(OpenIdProtocol::OpenIdProtocol_DHConsumerPublic) => $b64_public,
        );

        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $params);

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

    public function testAssociationSessionRequestDiffieHellmanSha256(){

        $b64_public = base64_encode(OpenIdCryptoHelper::convert($this->public,DiffieHellman::FORMAT_NUMBER,DiffieHellman::FORMAT_BTWOC));

        $this->assertTrue($b64_public === 'AIUmVPMheb/hEupD5m6veEEstnBVteyZPy+mlYX7ygxygLG/XuHFa8q4lZERJ9u1DNFOpXHRDq5RbjsaUYRDOtyrbkGbeKo5tPqjsynjXtoMAItxkxCU4jpQLvH85P+u7DeA0h3kKNHFa90ijZTIGSSDRF5wW9N+QPCUCt4G4xWZ');

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::AssociateMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocType) => OpenIdProtocol::SignatureAlgorithmHMAC_SHA256,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_SessionType) => OpenIdProtocol::AssociationSessionTypeDHSHA256,
            OpenIdProtocol::param(OpenIdProtocol::OpenIdProtocol_DHConsumerPublic) => $b64_public,
        );

        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $params);

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

    public function testAssociationSessionRequestNoEncryption(){

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::AssociateMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocType) => OpenIdProtocol::AssociationSessionTypeNoEncryption,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_SessionType) => OpenIdProtocol::AssociationSessionTypeNoEncryption,
        );

        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $params);

        $this->assertResponseStatus(200);

        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());

        $this->assertTrue(isset($openid_response['ns']));
        $this->assertTrue($openid_response['ns'] === OpenIdProtocol::OpenID2MessageType);
        $this->assertTrue(isset($openid_response['assoc_type']));
        $this->assertTrue($openid_response['assoc_type'] === OpenIdProtocol::AssociationSessionTypeNoEncryption);
        $this->assertTrue(isset($openid_response['session_type']));
        $this->assertTrue($openid_response['session_type'] === OpenIdProtocol::AssociationSessionTypeNoEncryption);
        $this->assertTrue(isset($openid_response['assoc_handle']) && ! empty($openid_response['assoc_handle']));
        $this->assertTrue(isset($openid_response['expires_in']));
        $this->assertTrue(isset($openid_response['mac_key']) && !empty($openid_response['mac_key']));

    }

    // test for authentication

    public function testAuthenticationSetupModePrivateAssociation(){
        //set login info
        $user = OpenIdUser::where('external_id', '=', 'smarcet@gmail.com')->first();
        Auth::login($user);
        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);


        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)     => "https://www.test.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)  => "https://www.test.com/oauth2",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)  => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
        );

        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $params);

        $this->assertResponseStatus(302);

        $openid_response = $this->parseOpenIdResponse($response->getTargetUrl());

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));

        //http://openid.net/specs/openid-authentication-2_0.html#check_auth
        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $this->prepareCheckAuthenticationParams($openid_response));
        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());
        $this->assertResponseStatus(200);
        $this->assertTrue($openid_response['is_valid'] === 'true');
    }


    public function testAuthenticationSetupModeSessionAssociationDHSha256(){

        $b64_public = base64_encode(OpenIdCryptoHelper::convert($this->public,DiffieHellman::FORMAT_NUMBER,DiffieHellman::FORMAT_BTWOC));

        $this->assertTrue($b64_public === 'AIUmVPMheb/hEupD5m6veEEstnBVteyZPy+mlYX7ygxygLG/XuHFa8q4lZERJ9u1DNFOpXHRDq5RbjsaUYRDOtyrbkGbeKo5tPqjsynjXtoMAItxkxCU4jpQLvH85P+u7DeA0h3kKNHFa90ijZTIGSSDRF5wW9N+QPCUCt4G4xWZ');

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::AssociateMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocType) => OpenIdProtocol::SignatureAlgorithmHMAC_SHA256,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_SessionType) => OpenIdProtocol::AssociationSessionTypeDHSHA256,
            OpenIdProtocol::param(OpenIdProtocol::OpenIdProtocol_DHConsumerPublic) => $b64_public,
        );

        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $params);

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

        //set login info
        $user = OpenIdUser::where('external_id', '=', 'smarcet@gmail.com')->first();
        Auth::login($user);
        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);


        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)     => "https://www.test.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)  => "https://www.test.com/oauth2",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)  => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocHandle) => $openid_response['assoc_handle'],
        );

        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $params);

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


    public function testAuthenticationCheckImmediateAuthenticationPrivateSession(){
        //set login info
        $user = OpenIdUser::where('external_id', '=', 'smarcet@gmail.com')->first();
        Auth::login($user);
        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);


        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::ImmediateMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)     => "https://www.test.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)  => "https://www.test.com/oauth2",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)  => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
        );

        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $params);

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


    //extension tests


    /**
     * test openid oauth2 extension
     * https://developers.google.com/accounts/docs/OpenID#oauth
     */

    public function testCheckSetupOAuth2ExtensionSubView(){

        //set login info
        $user = OpenIdUser::where('external_id', '=', 'smarcet@gmail.com')->first();
        Auth::login($user);

        $scope = array(
            sprintf('%s/api/resource-server/read',$this->current_realm),
            sprintf('%s/api/resource-server/read.page',$this->current_realm),
            sprintf('%s/api/resource-server/write',$this->current_realm),
            sprintf('%s/api/resource-server/delete',$this->current_realm),
            sprintf('%s/api/resource-server/update',$this->current_realm),
            sprintf('%s/api/resource-server/update.status',$this->current_realm),
            sprintf('%s/api/resource-server/regenerate.secret',$this->current_realm),
        );

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)     => "https://www.test.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)  => "https://www.test.com/oauth2",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)  => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
            //oauth2
            OpenIdOAuth2Extension::paramNamespace()                         => OpenIdOAuth2Extension::NamespaceUrl,
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::ClientId)   => $this->oauth2_client_id,
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::Scope)      => implode(' ',$scope),
        );

        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $params);

        $this->assertResponseStatus(302);

        $content = $response->getContent();
    }

    public function testCheckSetupOAuth2Extension(){

        //set login info
        $user = OpenIdUser::where('external_id', '=', 'smarcet@gmail.com')->first();
        Auth::login($user);
        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowForever);

        $scope = array(
            sprintf('%s/api/resource-server/read',$this->current_realm),
            sprintf('%s/api/resource-server/read.page',$this->current_realm),
            sprintf('%s/api/resource-server/write',$this->current_realm),
            sprintf('%s/api/resource-server/delete',$this->current_realm),
            sprintf('%s/api/resource-server/update',$this->current_realm),
            sprintf('%s/api/resource-server/update.status',$this->current_realm),
            sprintf('%s/api/resource-server/regenerate.secret',$this->current_realm),
        );

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)     => "https://www.test.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)  => "https://www.test.com/oauth2",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)  => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
            //oauth2
            OpenIdOAuth2Extension::paramNamespace()                         => OpenIdOAuth2Extension::NamespaceUrl,
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::ClientId)   => $this->oauth2_client_id,
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::Scope)      => implode(' ',$scope),
        );

        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $params);

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


        //oauth2

        $this->assertTrue(isset($openid_response[OpenIdOAuth2Extension::paramNamespace()]));
        $this->assertTrue($openid_response[OpenIdOAuth2Extension::paramNamespace()] === OpenIdOAuth2Extension::NamespaceUrl);

        $this->assertTrue(isset($openid_response[OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::RequestToken)]));
        $this->assertTrue(!empty($openid_response[OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::RequestToken)]));

        $this->assertTrue(isset($openid_response[OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::Scope)]));
        $this->assertTrue(!empty($openid_response[OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::Scope)]));


        //http://openid.net/specs/openid-authentication-2_0.html#check_auth
        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $this->prepareCheckAuthenticationParams($openid_response));
        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());
        $this->assertResponseStatus(200);
        $this->assertTrue($openid_response['is_valid'] === 'true');
    }

    public function testCheckSetupOAuth2ExtensionWrongClientId(){

        //set login info
        $user = OpenIdUser::where('external_id', '=', 'smarcet@gmail.com')->first();
        Auth::login($user);
        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        $scope = array(
            sprintf('%s/api/resource-server/read',$this->current_realm),
            sprintf('%s/api/resource-server/read.page',$this->current_realm),
            sprintf('%s/api/resource-server/write',$this->current_realm),
            sprintf('%s/api/resource-server/delete',$this->current_realm),
            sprintf('%s/api/resource-server/update',$this->current_realm),
            sprintf('%s/api/resource-server/update.status',$this->current_realm),
            sprintf('%s/api/resource-server/regenerate.secret',$this->current_realm),
        );

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)     => "https://www.test.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)  => "https://www.test.com/oauth2",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)  => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
            //oauth2
            OpenIdOAuth2Extension::paramNamespace()                         => OpenIdOAuth2Extension::NamespaceUrl,
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::ClientId)   => 'wrong_client_id',
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::Scope)      => implode(' ',$scope),
        );

        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $params);

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
        $response        = $this->action("POST", "OpenIdProviderController@op_endpoint", $this->prepareCheckAuthenticationParams($openid_response));
        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());
        $this->assertResponseStatus(200);
        $this->assertTrue($openid_response['is_valid'] === 'true');
    }

    public function testCheckSetupOAuth2ExtensionBadRequest(){

        //set login info
        $user = OpenIdUser::where('external_id', '=', 'smarcet@gmail.com')->first();
        Auth::login($user);
        Session::set("openid.authorization.response", IAuthService::AuthorizationResponse_AllowOnce);

        $scope = array(
            sprintf('%s/api/resource-server/read',$this->current_realm),
            sprintf('%s/api/resource-server/read.page',$this->current_realm),
            sprintf('%s/api/resource-server/write',$this->current_realm),
            sprintf('%s/api/resource-server/delete',$this->current_realm),
            sprintf('%s/api/resource-server/update',$this->current_realm),
            sprintf('%s/api/resource-server/update.status',$this->current_realm),
            sprintf('%s/api/resource-server/regenerate.secret',$this->current_realm),
        );

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)     => "https://www.test.com/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)  => "https://www.test.com/oauth2",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)  => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
            //oauth2
            OpenIdOAuth2Extension::paramNamespace()                         => OpenIdOAuth2Extension::NamespaceUrl,
            //missing client id
            //OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::ClientId)   => 'wrong_client_id',
            OpenIdOAuth2Extension::param(OpenIdOAuth2Extension::Scope)      => implode(' ',$scope),
        );

        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $params);

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
        $response        = $this->action("POST", "OpenIdProviderController@op_endpoint", $this->prepareCheckAuthenticationParams($openid_response));
        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());
        $this->assertResponseStatus(200);
        $this->assertTrue($openid_response['is_valid'] === 'true');
    }

}
