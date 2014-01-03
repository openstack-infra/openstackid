<?php

use openid\OpenIdProtocol;

class OpenIdProtocolTest extends TestCase
{

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
}
