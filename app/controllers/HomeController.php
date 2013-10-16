<?php
use openid\XRDS\XRDSDocumentBuilder;
class HomeController extends BaseController {

    public function index(){
        $value = Request::header('Content-Type');
        if($value == XRDSDocumentBuilder::ContentType)
            return Redirect::action('DiscoveryController@idp');
        return View::make("home");
    }
}