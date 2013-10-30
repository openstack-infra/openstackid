<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 4:20 PM
 * To change this template use File | Settings | File Templates.
 */

use openid\OpenIdProtocol;

class OpenIdProtocolTest extends TestCase {

    public function testCheckId_immediate_Invalid(){
        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::ImmediateMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)     => "*.uk",//invalid realm
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)  => "http://dev.openstack.org/login",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)  => "https://dev.openstackid.com/sebastian.marcet",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "https://dev.openstackid.com/sebastian.marcet",
        );

        $response      = $this->action("POST","OpenIdProviderController@op_endpoint",$params);
        $status        = $response->getStatusCode();
        $content       = $response->getContent();
        $target_url    = $response->getTargetUrl();

        $url           = explode('?',$target_url,2);
        $openid_response = array();
        $query_params  = explode('&',$url[1]);
        foreach($query_params as $param){
            $aux = explode('=',$param,2);
            $openid_response[$aux[0]] = $aux[1];
        }

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Error)]));
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));
        $this->assertTrue($status==302);
    }

    public function testCheckId_immediate(){


        $params = array(
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)        => OpenIdProtocol::OpenID2MessageType,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)      => OpenIdProtocol::ImmediateMode,
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)     => "http://dev.openstack.org",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)  => "http://dev.openstack.org/login",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)  => "https://dev.openstackid.com/sebastian.marcet",
            OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId) => "https://dev.openstackid.com/sebastian.marcet",
        );

        $response      = $this->action("POST","OpenIdProviderController@op_endpoint",$params);
        $status        = $response->getStatusCode();
        $content       = $response->getContent();
        $target_url    = $response->getTargetUrl();

        $url           = explode('?',$target_url,2);
        $openid_response = array();
        $query_params  = explode('&',$url[1]);
        foreach($query_params as $param){
            $aux = explode('=',$param,2);
            $openid_response[$aux[0]] = $aux[1];
        }

        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]));
        $mode = $openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)];
        $this->assertTrue($mode == OpenIdProtocol::SetupNeededMode);
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)]));
        $this->assertTrue(isset($openid_response[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)]));
        $this->assertTrue($status==302);
    }
}
