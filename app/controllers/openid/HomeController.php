<?php

/**
 * Class HomeController
 */
class HomeController extends OpenIdController
{

    private $discovery;

    public function __construct(DiscoveryController $discovery)
    {
        $this->discovery = $discovery;
    }

    public function index()
    {

        if ($this->isDiscoveryRequest())
            return $this->discovery->idp();
        if (Auth::guest())
            return View::make("home");
        else
            return Redirect::action("UserController@getProfile");
    }
}