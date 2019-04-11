@extends('layout')
@section('title')
    <title>Welcome to OpenStackId - Sign in </title>
@append
@section('meta')
    <meta http-equiv="X-XRDS-Location" content="{!! URL::action("OpenId\DiscoveryController@idp") !!}" />
@append
@section('content')
    @if(isset($identity_select))
        <legend style="margin-left: 15px;">
        @if(!$identity_select)
        Sign in to <b>{!! $realm !!}</b> using <b>{!! $identity !!}</b>
        @else
        Sign in to <b>{!! $realm !!} </b> using your OpenStackID
        @endif
        </legend>
    @endif

    <div id="cookies-disabled-dialog" class="alert alert-warning alert-dismissible" style="display: none;" role="alert">
        <button type="button" class="close"onclick="$('#cookies-disabled-dialog').hide()" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <strong>Warning!</strong> Cookies are not enabled, please enabled them in order to use OpenStackId.
    </div>

    <div class="col-md-4" id="sidebar">
        <div class="well">
            {!! Form::open(array('id'=>'login_form','url' => URL::action('UserController@postLogin'), 'method' => 'post',  "autocomplete" => "off")) !!}
                <legend>Welcome&nbsp;to&nbsp;OpenStackId!&nbsp;<span aria-hidden="true" style="font-size: 10pt;" class="glyphicon glyphicon-info-sign pointable" title="Please use your OpenStack ID to log in"></span></legend>
                <div class="form-group">
                    {!! Form::email('username',Session::has('username')? Session::get('username'):null, array
                    (
                        'placeholder'  => 'Username',
                        'class'        =>'form-control',
                        'required'     => 'true',
                        'autocomplete' => 'off'
                    )) !!}
                </div>
                <div class="form-group">
                    {!! Form::password('password', array
                    (
                        'placeholder'  => 'Password',
                        'class'        => 'form-control',
                        'required'     => 'true',
                        'autocomplete' => 'off'
                    )) !!}
                </div>
                <div class="form-group">
                    @if(Session::has('flash_notice'))
                            <span class="error-message"><i class="fa fa-exclamation-triangle">&nbsp;{!! Session::get('flash_notice') !!}</i></span>
                    @else
                        @foreach($errors->all() as $message)
                                <span class="error-message"><i class="fa fa-exclamation-triangle">&nbsp;{!! $message !!}</i></span>
                        @endforeach
                    @endif
                </div>
                <div class="checkbox">
                    <label class="checkbox">
                        {!! Form::checkbox('remember', '1', false) !!}Remember me
                    </label>
                </div>
                <div class="pull-right">
                    {!! Form::submit('Sign In',array('id'=>'login','class'=>'btn btn-primary')) !!}
                    <a class="btn btn-primary" href="{!! URL::action('UserController@cancelLogin') !!} ">Cancel</a>
                </div>
                <div style="clear:both;padding-top:15px;" class="row">
                    <div class="col-md-12">
                        <a title="forgot password" target="_blank" href="{!! ExternalUrlService::getForgotPasswordUrl() !!}">Forgot password?</a>
                    </div>
                </div>
            <div style="clear:both;padding-top:15px;" class="row">
                <div class="col-md-12">
                    <a title="register new account" target="_blank" href="{!! ExternalUrlService::getCreateAccountUrl() !!}">Register for an OpenStack ID</a>
                </div>
            </div>
            <div style="clear:both;padding-top:15px;" class="row">
                <div class="col-md-12">
                    <a title="verify account" target="_blank" href="{!! ExternalUrlService::getVerifyAccountUrl() !!}">Verify OpenStack ID</a>
                </div>
            </div>
            {!!  GoogleReCaptchaV3::renderField('login_id','login') !!}
            {!! Form::hidden('login_attempts', Session::get('login_attempts')) !!}
            </fieldset>
            {!! Form::close() !!}
        </div>
    </div>
    <div class="col-md-8">
    </div>
@append
@section('scripts')
    {!!  GoogleReCaptchaV3::init() !!}
    {!! HTML::script('assets/js/login.js') !!}
@append