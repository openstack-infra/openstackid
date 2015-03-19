<?php
use openid\XRDS\XRDSDocumentBuilder;

class HomeController extends BaseController
{

    private $discovery;

    public function __construct(DiscoveryController $discovery)
    {
        $this->discovery = $discovery;
    }

    public function index()
    {
        //This field contains a semicolon-separated list of representation schemes
        //which will be accepted in the response to this request.
        $accept          = Request::header('Accept');
        if (strstr($accept, XRDSDocumentBuilder::ContentType))
            return $this->discovery->idp();
        if (Auth::guest())
            return View::make("home");
        else {
            return Redirect::action("UserController@getProfile");
        }
    }
}