@extends('layout')
@section('title')
    <title>Welcome to openstackId - OpenStack ID Check Session Frame</title>
@stop
@section('content')
    <div class="container">
    </div>
@stop
@section('scripts')
    {{ HTML::script('bower_assets/crypto-js/crypto-js.js')}}
    {{ HTML::script('bower_assets/jquery-cookie/jquery.cookie.js')}}
    {{ HTML::script('assets/js/oauth2/session/check.session.js')}}
@append