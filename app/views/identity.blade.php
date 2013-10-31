@extends('layout')
@section('content')
<h1>OpenstackId Idp</h1>
<div class="container">
    @if(Auth::guest())
        @if( $show_fullname === false && $show_email===false && $show_pic == false)
        <p>This is an OpenID Identity page. This user has chosen not to display any information on this page.</p>
        @else
                @if( $show_fullname )
                <legend>{{ $username }}</legend>
                @endif
                @if( $show_pic && !empty($pic))
                <div class="row">
                    <div class="span4">
                        <img src="{{ $pic }}" class="img-polaroid">
                    </div>
                </div>
                @endif
                @if( $show_email )
                    <div class="row">
                        <div class="span4">
                             <i class="icon-envelope"></i>
                            <a href="mailto:{{ $email }}">{{ $email }}</a>
                        </div>
                    </div>
                @endif
        @endif
    @else
        Welcome, {{{ $username }}}.
        <a href="{{ URL::action("UserController@logout") }}"">logout</a>
        @if( $show_fullname === false && $show_email ===false)
        <p>
            This is your identity page. You are currently displaying no information on this page. You can display information such as your name, contact info and a photo.
        </p>
        @else
            @if( $show_fullname )
            <legend>{{ $username }}</legend>
            @endif
            @if( $show_pic && !empty($pic))
            <div class="row">
                <div class="span4">
                    <img src="{{ $pic }}" class="img-polaroid">
                </div>
            </div>
            @endif
            @if( $show_email )
            <div class="row">
                <div class="span4">
                    <i class="icon-envelope"></i>
                    <a href="mailto:{{ $email }}">{{ $email }}</a>
                </div>
            </div>
            @endif
        @endif
        <div class="row">
            <div class="span6">
                <a href="{{ URL::action("UserController@getProfile") }}"">edit your profile</a>
            </div>
        </div>
    @endif
</div>
@stop