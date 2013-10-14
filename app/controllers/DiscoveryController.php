<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 12:29 PM
 * To change this template use File | Settings | File Templates.
 */

use openid\IOpenIdProtocol;

class DiscoveryController extends BaseController {

    private $openid_protocol;

    public function __construct(IOpenIdProtocol $openid_protocol){
        $this->openid_protocol=$openid_protocol;
    }

    /**
     * XRDS discovery(eXtensible Resource Descriptor Sequence)
     * @return xrds document on response
     */
    public function idp(){
        $response = Response::make($this->openid_protocol->getXRDSDiscovery(), 200);
        $response->header('Content-Type', "application/xrds+xml");
        return $response;
    }

    public function user(){

    }

}