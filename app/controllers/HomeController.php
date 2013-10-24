<?php
use openid\XRDS\XRDSDocumentBuilder;
class HomeController extends BaseController {

    public function index(){
        //This field contains a semicolon-separated list of representation schemes
        //which will be accepted in the response to this request.
        $accept = Request::header('Accept');
        $accept_values = explode(",",$accept);
        if(in_array(XRDSDocumentBuilder::ContentType,$accept_values))
            return Redirect::action('DiscoveryController@idp');
        if(Auth::guest())
            return View::make("home");
        else{
            return Redirect::action("UserController@getProfile");
        }
    }
}