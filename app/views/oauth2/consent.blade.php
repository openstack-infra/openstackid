@extends('layout')
@section('title')
<title>Welcome to OpenStackId - Request for Permission </title>
@stop

@section('header_right')
    @if(Auth::check())
        <div class="row">
            <div class="col-md-6 col-md-offset-8">
                Welcome, <a href="{{ URL::action("UserController@getProfile") }}">{{Auth::user()->identifier}}</a>
            </div>
        </div>
    @endif
@stop

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="row">
                <div class="col-md-3">
                    <img src="{{$app_logo}}"  class="img-rounded" border="0"/>
                </div>
                <div class="col-md-9">
                    <h2>{{$app_name}}&nbsp; <span class="glyphicon glyphicon-info-sign pointable info" aria-hidden="true" data-content="Developer Email: <a href='mailto:{{$dev_info_email}}'>{{$dev_info_email}}</a>.<br> Clicking 'Accept' will redirect you to: <a href='{{$website}}' target='_blank'>{{$website}}</a>" title="Developer Info"></span></h2>
                </div>
            </div>
            <legend>This app would like to:</legend>
            <ul class="unstyled list-inline">
            @foreach ($requested_scopes as $scope)
                <li> {{$scope->short_description}}&nbsp;<span class="glyphicon glyphicon-info-sign pointable info" aria-hidden="true" data-content="{{ $scope->description }}" title="Scope Info"></span></li>
            @endforeach
            </ul>
            <p class="privacy-policy">
                ** <b>{{$app_name}}</b> Application and <b>Openstack</b> will use this information in accordance with their respective terms of service and privacy policies.
            </p>
            {{ Form::open(array('url' => URL::action("UserController@postConsent") ,'id'=>'authorization_form', 'method' => 'post',  "autocomplete" => "off")) }}
                <input type="hidden"  name='trust' id='trust' value=""/>
                <button class="btn btn-default btn-md" id="cancel-authorization" type="button">Cancel</button>
                <button class="btn btn-primary btn-md" id="approve-authorization" type="button">Accept</button>
            {{ Form::close() }}
        </div>
    </div>
</div>
@stop
@section('scripts')
{{ HTML::script('assets/js/oauth2/consent.js') }}
@stop