@extends('layout')

@section('title')
    <title>Welcome to openstackId - OpenStack ID Logout</title>
@stop

@section('content')
    <div class="container">
        <p>Your Session at OpenstackID had ended!</p>
    </div>
@stop
@section('scripts')
    {{ HTML::script('bower_assets/crypto-js/crypto-js.js')}}
    {{ HTML::script('bower_assets/jquery-cookie/jquery.cookie.js')}}
@append