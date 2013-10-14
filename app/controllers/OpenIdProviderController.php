<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 6:05 PM
 * To change this template use File | Settings | File Templates.
 */

use openid\IOpenIdProtocol;
use \Illuminate\Support\Facades\Input;
use openid\OpenIdMessage;

class OpenIdProviderController extends BaseController{

    private $openid_protocol;

    public function __construct(IOpenIdProtocol $openid_protocol){
        $this->openid_protocol = $openid_protocol;
    }

    public function op_endpoint(){
       $msg = new OpenIdMessage(Input::all());
       if($msg->IsValid()){
            $this->openid_protocol->HandleOpenIdMessage($msg);
       }
       if( isset($msg["openid.return_to"])){
           //Indirect Error Response
           //sent response by
       }
    }
}