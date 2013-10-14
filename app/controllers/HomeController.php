<?php

class HomeController extends BaseController {

    public function index(){
        return Redirect::action('DiscoveryController@idp');
    }
}