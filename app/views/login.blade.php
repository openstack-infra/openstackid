@extends('layout')
@section('title')
    <title>Welcome to OpenStackId - Sign in </title>
@append
@section('meta')
    <meta http-equiv="X-XRDS-Location" content="{{ URL::action("DiscoveryController@idp")}}" />
@append
@section('content')

    <h4 style="margin-left: 15px;">Please use your OpenStack ID to log in</h4>
    @if(isset($identity_select))
        <legend style="margin-left: 15px;">
        @if(!$identity_select)
        Sign in to <b>{{$realm}}</b> using <b>{{$identity}}</b>
        @else
        Sign in to <b>{{$realm}}</b> using your OpenStackID
        @endif
        </legend>
    @endif

    <div class="col-md-4" id="sidebar">
        <div class="well">
            {{ Form::open(array('id'=>'login_form','url' => URL::action('UserController@postLogin'), 'method' => 'post',  "autocomplete" => "off")) }}

                <legend>Login</legend>
                <div class="form-group">
                    {{ Form::text('username',Session::has('username')? Session::get('username'):null, array('placeholder' => 'Username','class'=>'form-control')) }}
                </div>
                <div class="form-group">
                    {{ Form::password('password', array('placeholder' => 'Password','class'=>'form-control')) }}
                </div>
                @if(Session::has('login_attempts') && Session::has('max_login_attempts_2_show_captcha') && Session::get('login_attempts') > Session::get('max_login_attempts_2_show_captcha'))
                    {{ Form::captcha(array('id'=>'captcha','class'=>'input-block-level')) }}
                    {{ Form::hidden('login_attempts', Session::get('login_attempts')) }}
                @else
                    {{ Form::hidden('login_attempts', '0') }}
                @endif
                <div class="checkbox">
                    <label class="checkbox">
                        {{ Form::checkbox('remember', '1', false) }}Remember me
                        for @if(ServerConfigurationService::getConfigValue("Remember.ExpirationTime")<60) }}
                        {{ ServerConfigurationService::getConfigValue("Remember.ExpirationTime") }} Minutes
                        @elseif(ServerConfigurationService::getConfigValue("Remember.ExpirationTime")>60 && ServerConfigurationService::getConfigValue("Remember.ExpirationTime")< (24*60))
                            {{ intval(ServerConfigurationService::getConfigValue("Remember.ExpirationTime")/60) }} Hours
                        @elseif(ServerConfigurationService::getConfigValue("Remember.ExpirationTime")> (24*60) )
                            {{ intval(ServerConfigurationService::getConfigValue("Remember.ExpirationTime")/(24*60)) }} Days
                        @endif
                    </label>
                </div>
                <div class="pull-right">
                    {{ Form::submit('Sign In',array('id'=>'login','class'=>'btn btn-primary')) }}
                    <a class="btn btn-primary" href="{{ URL::action('UserController@cancelLogin') }}">Cancel</a>
                </div>
                <div style="clear:both;padding-top:15px;" class="row">
                    <div class="col-md-12">
                        <a title="forgot password" target="_blank" href="{{ ExternalUrlService::getForgotPasswordUrl() }}">Forgot password?</a>
                    </div>

                </div>
            <div style="clear:both;padding-top:15px;" class="row">
                <div class="col-md-12">
                    <a title="register new account" target="_blank" href="{{ ExternalUrlService::getCreateAccountUrl() }}">Register for an OpenStack ID</a>
                </div>
            </div>
            <div style="clear:both;padding-top:15px;" class="row">
                <div class="col-md-12">
                    <a title="verify account" target="_blank" href="{{ ExternalUrlService::getVerifyAccountUrl() }}">Verify OpenStack ID</a>
                </div>
            </div>
            </fieldset>
            {{ Form::close() }}
        </div>
        @if(Session::has('flash_notice'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                {{ Session::get('flash_notice')  }}
            </div>
        @else
            @foreach($errors->all() as $message)
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    {{ $message }}
                </div>
            @endforeach
        @endif
    </div>
    <div class="col-md-8">
    </div>
@append
@section('scripts')
    {{ HTML::script('assets/js/login.js') }}
@append