@extends('layout')
@section('content')
<h1>OpenstackId Idp</h1>
<div class="container">
    @if( Auth::guest())
    <p>This is an OpenID Identity page. This user has chosen not to display any information on this page.</p>
    @else
        Welcome, {{{ $username }}}.
        <a href="{{ URL::action("UserController@logout") }}"">logout</a>
        <p>
            This is your identity page. You are currently displaying no information on this page. You can display information such as your name, contact info, a short description of yourself, and a photo.
        </p>
        <a href="{{ URL::action("UserController@getProfile") }}"">edit your profile</a>
    @endif
</div>
@stop