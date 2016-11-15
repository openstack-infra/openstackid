@extends('layout')
@section('title')
    <title>Welcome to openStackId</title>
@stop
@section('meta')
    <meta http-equiv="X-XRDS-Location" content="{!! URL::action("OpenId\DiscoveryController@idp") !!}" />
@append
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <h1>OpenStackId Identity Provider</h1>
                <div class="panel">
                    <div class="panel-heading strong">Log in to OpenStack</div>
                    <div class="row" style="text-align: center;">
                        <div class="col-md-12">
                            <div class="row" style="padding-top: 5px;padding-bottom: 5px;">
                                <div class="col-md-12">
                                    <a href="{!! URL::action("UserController@getLogin") !!}" class="btn btn-default btn-md active">Sign in to your account</a>
                                </div>
                            </div>
                            <div class="row" style="padding-top: 5px;padding-bottom: 5px;">
                                <div class="col-md-12">
                                    <a href="{!! ExternalUrlService::getCreateAccountUrl() !!}" class="btn btn-default btn-md active">Register for an OpenStack ID</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-info margin-top-20">Once you're signed in, you can manage your trusted sites, change
                        your settings and more.</p>
                </div>
            </div>
        </div>
    </div>
@stop