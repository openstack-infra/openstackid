@extends('layout')
@section('title')
    <title>Welcome to openstackId</title>
@stop
@section('meta')
    <meta http-equiv="X-XRDS-Location" content="{{ URL::action("DiscoveryController@idp")}}" />
@append
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <h1>OpenstackId Identity Provider</h1>
                <div class="panel">
                    <div class="panel-heading strong">Log in to OpenStack</div>
                    <div style="text-align: center">
                        <a href="{{ URL::action("UserController@getLogin")}}" class="btn btn-default btn-md active">Sign in to your account</a>
                        <a href="{{ ServerConfigurationService::getConfigValue("Assets.Url") }}join/register"
                           class="btn btn-default btn-md active">Register for an OpenStack ID</a>
                    </div>
                    <p class="text-info margin-top-20">Once you're signed in, you can manage your trusted sites, change
                        your settings and more.</p>
                </div>
            </div>
        </div>
    </div>
@stop