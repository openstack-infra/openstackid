@extends('layout')
@section('title')
<title>Welcome to openstackId</title>
@stop
@section('content')
<div class="container">
    @if(Auth::guest() || $another_user)
        @if( $show_fullname === 0 && $show_email===0 && $show_pic === 0)
        <p>This is an OpenID Identity page. This user has chosen not to display any information on this page.</p>
        @else
                @if( $show_fullname )
                <legend>{{ $username }}</legend>
                @endif
                @if( $show_pic && !empty($pic))
                <div class="row-fluid">
                    <div class="span4">
                        <img src="{{ $pic }}" class="img-polaroid">
                    </div>
                </div>
                @endif
                @if( $show_email )
                    <div class="row-fluid email-row">
                        <div class="span4">
                             <i class="icon-envelope"></i>
                            <a href="mailto:{{ $email }}">{{ $email }}</a>
                        </div>
                    </div>
                @endif
        @endif
    @else
        Welcome, {{{ $username }}}.
        <a class="btn btn-small" href="{{ URL::action("UserController@getProfile") }}"">edit your profile</a>
        <a class="btn btn-small" href="{{ URL::action("UserController@logout") }}"">logout</a>
        @if( $show_fullname === 0 && $show_email===0 && $show_pic === 0)
        <p>
            This is your identity page. You are currently displaying no information on this page. You can display information such as your name, contact info and a photo.
        </p>
        @else
            @if( $show_fullname )
            <legend>{{ $username }}</legend>
            @endif
            @if( $show_pic && !empty($pic))
            <div class="row-fluid">
                <div class="span4">
                    <img src="{{ $pic }}" class="img-polaroid">
                </div>
            </div>
            @endif
            @if( $show_email )
            <div class="row-fluid email-row">
                <div class="span4">
                    <i class="icon-envelope"></i>
                    <a href="mailto:{{ $email }}">{{ $email }}</a>
                </div>
            </div>
            @endif
        @endif
    @endif
</div>
@stop