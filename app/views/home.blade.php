@extends('layout')
@section('title')
<title>Welcome to openstackId</title>
@stop
@section('content')
<div class="container-fluid">
    <div class="row-fluid">
        <div class="span6 offset3">

            <h1>OpenstackId Identity Provider</h1>
            <p>Welcome to OpenstackId!!!</p>
            <div class="panel">
                <div class="panel-heading strong">Got one? Good ...</div>
                <a href="{{ URL::action("UserController@getLogin")}}" class="btn">Sign in to your account</a>
                <p class="text-info margin-top-20">Once you're signed in, you can manage your trusted sites, change your settings and more.</p>
            </div>
        </div>
    </div>
</div>
@stop