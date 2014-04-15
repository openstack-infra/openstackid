@extends('layout')
@section('title')
<title>Welcome to openstackId - Request for Permission </title>
@stop

@section('header_right')
    @if(Auth::check())
        <div class="row-fluid">
            <div class="span6 offset8">
                Welcome, <a href="{{ URL::action("UserController@getProfile") }}">{{Auth::user()->identifier}}</a>
            </div>
        </div>
    @endif
@stop

@section('content')
<div class="container">
    <div class="row-fluid">
        <div class="span6 offset3">
            <div class="row-fluid">
                <div class="span2">
                    <img src="{{$app_logo}}" border="0"/>
                </div>
                <div class="span9">
                    <h2>{{$app_name}}&nbsp;</h2>
                </div>
                <div class="span1">
                    <i data-content="Developer Email: <a href='mailto:{{$dev_info_email}}'>{{$dev_info_email}}</a>.<br> Clicking 'Accept' will redirect you to: <a href='{{$website}}' target='_blank'>{{$website}}</a>" class="icon-info-sign info" title="Developer Info"></i>
                </div>
            </div>
            <legend>This app would like to:</legend>
            <ul class="unstyled list-inline">
            @foreach ($requested_scopes as $scope)
                <li> {{$scope->short_description}}&nbsp;<i class="icon-info-sign info" data-content="{{ $scope->description }}" title="Scope Info"></i></li>
            @endforeach
            </ul>
            <p class="privacy-policy">
                ** <b>{{$app_name}}</b> Application and <b>Openstack</b> will use this information in accordance with their respective terms of service and privacy policies.
            </p>
            {{ Form::open(array('url' => URL::action("UserController@postConsent") ,'id'=>'authorization_form', 'method' => 'post',  "autocomplete" => "off")) }}
                <input type="hidden"  name='trust' id='trust' value=""/>
                <button class="btn" id="cancel-authorization" type="button">Cancel</button>
                <button class="btn btn-primary" id="approve-authorization" type="button">Accept</button>
            {{ Form::close() }}
        </div>
    </div>
</div>
@stop
@section('scripts')
{{ HTML::script('js/oauth2/consent.js') }}
@stop