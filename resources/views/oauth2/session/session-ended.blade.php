@extends('layout')

@section('title')
    <title>Welcome to OpenStackId - OpenStack ID Logout</title>
@stop

@section('content')
    <div class="container">
        <p>Your Session at OpenStackId had ended!</p>
    </div>
@stop
@section('scripts')
    {!! HTML::script('assets/crypto-js/crypto-js.js')!!}
    {!! HTML::script('assets/jquery-cookie/jquery.cookie.js')!!}
@append