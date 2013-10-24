@extends('layout')
@section('content')
<h1>OpenstackId Idp</h1>
<div class="container">
    Hello, {{{ $username }}}.
    <a href="{{ URL::action("UserController@logout") }}"">logout</a>
    <div>Your OPENID: {{$openid_url}}</div>
    @if (count($sites)>0)
    <div id="trusted_sites">
        <h3>Trusted Sites</h3>
        <ul>
        @foreach ($sites as $site)
            <li><div><span>Realm {{ $site->getRealm() }} - Policy {{ $site->getAuthorizationPolicy() }}</span>&nbsp;<a href="#">Edit</a>&nbsp;<a href="#">Delete</a></div></li>
        @endforeach
        </ul>
    </div>
    @endif
</div>
@stop