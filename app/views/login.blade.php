@extends('layout')
@section('title')
<title>Welcome to openstackId - Sign in </title>
@stop
@section('content')

<h4>Welcome to OpenstackId!!!</h4>
@if(isset($identity_select) && !$identity_select)
<legend>
    Sign in to <b>{{$realm}}</b> using <b>{{$identity}}</b>
</legend>
@endif

<div class="span4" id="sidebar">
    <div class="well">
        {{ Form::open(array('url' => URL::action('UserController@postLogin'), 'method' => 'post',  "autocomplete" => "off")) }}
        <fieldset>
            <legend>Login</legend>
            {{ Form::text('username',null, array('placeholder' => 'Username','class'=>'input-block-level')) }}
            {{ Form::password('password', array('placeholder' => 'Password','class'=>'input-block-level')) }}
            @if(Session::has('login_attempts') && Session::has('max_login_attempts_2_show_captcha') && Session::get('login_attempts') > Session::get('max_login_attempts_2_show_captcha'))
                {{ Form::captcha(array('id'=>'captcha','class'=>'input-block-level')) }}
                {{ Form::hidden('login_attempts', Session::get('login_attempts')) }}
            @else
                {{ Form::hidden('login_attempts', '0') }}
            @endif
            <label class="checkbox">
                {{ Form::checkbox('remember', '1', false) }}Remember me
            </label>
            <div class="pull-right">
                {{ Form::submit('Sign In',array('id'=>'login','class'=>'btn btn-primary')) }}
                <a class="btn btn-primary" href="{{ URL::action('UserController@cancelLogin') }}">Cancel</a>
            </div>
        </fieldset>
        {{ Form::close() }}
    </div>

    @if(Session::has('flash_notice'))
    <div class="alert alert-error">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ Session::get('flash_notice')  }}
    </div>
    @else
    @foreach($errors->all() as $message)
    <div class="alert alert-error">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ $message }}
    </div>
    @endforeach
    @endif

</div>
<div class="span8">

</div>

@stop