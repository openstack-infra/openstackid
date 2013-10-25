<?php
use openid\XRDS\XRDSDocumentBuilder;
class HomeController extends BaseController {

    private $discovery;
    public function __construct(DiscoveryController $discovery){
        $this->discovery=$discovery;
    }

    public function index(){
        //This field contains a semicolon-separated list of representation schemes
        //which will be accepted in the response to this request.
        $accept = Request::header('Accept');
        $accept_values = explode(",",$accept);
        if(in_array(XRDSDocumentBuilder::ContentType,$accept_values))
            return $this->discovery->idp();
        if(Auth::guest())
            return View::make("home");
        else{
            return Redirect::action("UserController@getProfile");
        }
    }
}