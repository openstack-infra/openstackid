<?php

use openid\OpenIdProtocol;
use auth\OpenIdUser;
use oauth2\OAuth2Protocol;
use utils\services\IAuthService;
use openid\extensions\implementations\OpenIdOAuth2Extension;

class OpenIdProtocolTest extends TestCase
{
    private $current_realm;

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
            $openid_response[$aux[0]] = $aux[1];
        }
        return $openid_response;
    }

    public function testCheckId_immediate_Invalid()
    {
        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::ImmediateMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm) => "*.uk", //invalid realm
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo) => "http://dev.openstack.org/login",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity) => "https://dev.openstackid.com/sebastian.marcet",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "https://dev.openstackid.com/sebastian.marcet",
        );

        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $params);
        $status = $response->getStatusCode();
        $content = $response->getContent();
        $target_url = $response->getTargetUrl();

        $url = explode('?', $target_url, 2);
        $openid_response = array();
        $query_params = explode('&', $url[1]);
        foreach ($query_params as $param) {
            $aux = explode('=', $param, 2);
            $openid_response[$aux[0]] = $aux[1];
        }

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Error)]));
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));
        $this->assertTrue($status == 302);
    }

    public function testCheckId_immediate()
    {


        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::ImmediateMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm) => "http://dev.openstack.org",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo) => "http://dev.openstack.org/login",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity) => "https://dev.openstackid.com/sebastian.marcet",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "https://dev.openstackid.com/sebastian.marcet",
        );

        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $params);
        $status = $response->getStatusCode();
        $content = $response->getContent();
        $target_url = $response->getTargetUrl();

        $url = explode('?', $target_url, 2);
        $openid_response = array();
        $query_params = explode('&', $url[1]);
        foreach ($query_params as $param) {
            $aux = explode('=', $param, 2);
            $openid_response[$aux[0]] = $aux[1];
        }

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));
        $mode = $openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)];
        $this->assertTrue($mode == OpenIdProtocol::SetupNeededMode);
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));
        $this->assertTrue($status == 302);
    }


    public function testCheckSetup()
    {

        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS) => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode) => OpenIdProtocol::SetupMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm) => "http://drupal-openid.local/",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo) => "http://drupal-openid.local/openid/authenticate?destination=%3Cfront%3E",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity) => "http://specs.openid.net/auth/2.0/identifier_select",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "http://specs.openid.net/auth/2.0/identifier_select",
        );

        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $params);
        $status = $response->getStatusCode();
        $content = $response->getContent();
        $target_url = $response->getTargetUrl();

        $url = explode('?', $target_url, 2);
        $openid_response = array();
        $query_params = explode('&', $url[1]);
        foreach ($query_params as $param) {
            $aux = explode('=', $param, 2);
            $openid_response[$aux[0]] = $aux[1];
        }

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));
        $mode = $openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)];
        $this->assertTrue($mode == OpenIdProtocol::SetupNeededMode);
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));
        $this->assertTrue($status == 302);
    }


    /**
     * test openid oauth2 extension
     * https://developers.google.com/accounts/docs/OpenID#oauth
     */
    public function testCheckSetupOAuth2Ext(){

        $client_id = 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client';
        $client_secret = 'ITc/6Y5N7kOtGKhg';

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
            OpenIdOAuth2Extension::paramNamespace()                               =>OpenIdOAuth2Extension::NamespaceUrl,
            OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_ClientId) => $client_id,
            OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_Scope)    => implode(' ',$scope),
            OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_State)    => '123456'
        );

        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $params);

        $openid_response = $this->parseOpenIdResponse($response->getTargetUrl());

        $this->assertResponseStatus(302);
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));
        $this->assertTrue(isset($openid_response[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_State)]));
        $this->assertTrue($openid_response[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_State)]==='123456');
        $this->assertTrue(isset($openid_response[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_ResponseType_Code)]));
        $auth_code = $openid_response[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_ResponseType_Code)];
        $this->assertTrue(!empty($auth_code));

        //http://openid.net/specs/openid-authentication-2_0.html#check_auth
        $response = $this->action("POST", "OpenIdProviderController@op_endpoint", $this->prepareCheckAuthenticationParams($openid_response));
        $openid_response = $this->getOpenIdResponseLineBreak($response->getContent());
        $this->assertResponseStatus(200);
        $this->assertTrue($openid_response['is_valid'] === 'true');
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
        foreach($openid_response as $key => $value){
            if(!array_key_exists($key,$params))
                $params[$key] = @urldecode($value);
        }
        return $params;
    }

}
