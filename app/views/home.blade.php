@extends('layout')
@section('content')
<h1>OpenstackId Idp</h1>
<div class="container">
        <p>Welcome to OpenstackId Idp!!!</p>
        <a href="{{ URL::action("UserController@getLogin")}}">login</a>
</div>
@stop