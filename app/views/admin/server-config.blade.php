@extends('layout')

@section('title')
<title>Welcome to OpenStackId - Server Admin - Configuration</title>
@stop

@section('content')
@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
<div class="row">
    <div class="col-md-6">
        <form method="POST" id="form-server-configuration">
            <legend>General Configuration</legend>
            <div class="form-group">
                <label for="general-max-failed-login-attempts">MaximunFailed Login Attempts&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="in seconds"></span></label>
                <input class="form-control" type="number" min="5" step="1" id="general-max-failed-login-attempts" name="general-max-failed-login-attempts" value="{{$config_values['MaxFailed.Login.Attempts']}}"/>
            </div>
            <div class="form-group">
                <label for="general-max-failed-login-attempts-captcha">MaximunFailed Login Attempts To Show Captcha&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="in seconds"></span></label>
                <input class="form-control" type="number" min="3" step="1" id="general-max-failed-login-attempts-captcha" name="general-max-failed-login-attempts-captcha" value="{{$config_values['MaxFailed.LoginAttempts.2ShowCaptcha']}}"/>
            </div>
            <legend>OPENID Configuration</legend>
            <div class="form-group">
                <label for="openid-private-association-lifetime">Private Association Lifetime&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="in seconds"></span></label>
                <input class="form-control" type="number" min="240" max="512" step="1" id="openid-private-association-lifetime" name="openid-private-association-lifetime" value="{{$config_values['OpenId.Private.Association.Lifetime']}}"/>
            </div>
            <div class="form-group">
                <label for="openid-session-association-lifetime">Session Association Lifetime&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="in seconds"></span></label>
                <input class="form-control" type="number" min="21600" step="1"  id="openid-session-association-lifetime" name="openid-session-association-lifetime" value="{{$config_values['OpenId.Session.Association.Lifetime']}}"/>
            </div>
            <div class="form-group">
                <label for="openid-nonce-lifetime">Nonce Lifetime&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="in seconds"></span></label>
                <input class="form-control" type="number" min="360" max="512" step="1"  id="openid-nonce-lifetime" name="openid-nonce-lifetime" value="{{$config_values['OpenId.Nonce.Lifetime']}}"/>
            </div>
            <legend>OAUTH2 Configuration</legend>
            <div class="form-group">
                <label for="oauth2-auth-code-lifetime">Authorization Code Lifetime&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="in seconds"></span></label>
                <input class="form-control" type="number" min="60" max="600" step="1" id="oauth2-auth-code-lifetime" name="oauth2-auth-code-lifetime" value="{{$config_values['OAuth2.AuthorizationCode.Lifetime']}}"/>
            </div>
            <div class="form-group">
                <label for="oauth2-access-token-lifetime">Access Token Lifetime&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="in seconds"></span></label>
                <input class="form-control" type="number" min="60" step="1" id="oauth2-access-token-lifetime" name="oauth2-access-token-lifetime" value="{{$config_values['OAuth2.AccessToken.Lifetime']}}"/>
            </div>
            <div class="form-group">
                <label for="oauth2-id-token-lifetime">Id Token Lifetime&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="in seconds"></span></label>
                <input class="form-control" type="number" min="60" step="1" id="oauth2-id-token-lifetime" name="oauth2-id-token-lifetime" value="{{$config_values['OAuth2.IdToken.Lifetime']}}"/>
            </div>
            <div class="form-group">
                <label for="oauth2-refresh-token-lifetime">Refresh Token Lifetime&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="in seconds - zero value means infinite"></span></label>
                <input class="form-control" type="number" min="0" step="1" id="oauth2-refresh-token-lifetime" name="oauth2-refresh-token-lifetime" value="{{$config_values['OAuth2.RefreshToken.Lifetime']}}"/>
            </div>
            <div class="form-group">
                <label for="oauth2-id-access-token-revoked-lifetime">Revoked Access Token Lifetime&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="in seconds"></span></label>
                <input class="form-control" type="number" min="600" step="1" id="oauth2-id-access-token-revoked-lifetime" name="oauth2-id-access-token-revoked-lifetime" value="{{$config_values['OAuth2.AccessToken.Revoked.Lifetime']}}"/>
            </div>
            <div class="form-group">
                <label for="oauth2-id-access-token-void-lifetime">Void Access Token Lifetime&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="in seconds"></span></label>
                <input class="form-control" type="number" min="600" step="1" id="oauth2-id-access-token-void-lifetime" name="oauth2-id-access-token-void-lifetime" value="{{$config_values['OAuth2.AccessToken.Void.Lifetime']}}"/>
            </div>
            <div class="form-group">
                <label for="oauth2-id-refresh-token-revoked-lifetime">Revoked Refresh Token Lifetime&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="in seconds"></span></label>
                <input class="form-control" type="number" min="600" step="1" id="oauth2-id-refresh-token-revoked-lifetime" name="oauth2-id-refresh-token-revoked-lifetime" value="{{$config_values['OAuth2.RefreshToken.Revoked.Lifetime']}}"/>
            </div>
            <legend>OAUTH2 Security Policies Configuration</legend>
            <div class="form-group">
                <label for="oauth2-id-security-policy-minutes-without-exceptions">Minutes without exceptions rate&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="in minutes"></span></label>
                <input class="form-control" type="number" min="1" step="1" id="oauth2-id-security-policy-minutes-without-exceptions" name="oauth2-id-security-policy-minutes-without-exceptions" value="{{$config_values['OAuth2SecurityPolicy.MinutesWithoutExceptions']}}"/>
            </div>
            <div class="form-group">
                <label for="oauth2-id-security-policy-max-bearer-token-disclosure-attempts">Max. Bearer token disclousure exceptions attemps&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="in attemps"></span></label>
                <input class="form-control" type="number" min="3" step="1" id="oauth2-id-security-policy-max-bearer-token-disclosure-attempts" name="oauth2-id-security-policy-max-bearer-token-disclosure-attempts" value="{{$config_values['OAuth2SecurityPolicy.MaxBearerTokenDisclosureAttempts']}}"/>
            </div>
            <div class="form-group">
                <label for="oauth2-id-security-policy-max-invalid-client-exception-attempts">Max. invalid client exception attemps&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="in attemps"></span></label>
                <input class="form-control" type="number" min="3" step="1" id="oauth2-id-security-policy-max-invalid-client-exception-attempts" name="oauth2-id-security-policy-max-invalid-client-exception-attempts" value="{{$config_values['OAuth2SecurityPolicy.MaxInvalidClientExceptionAttempts']}}"/>
            </div>
            <div class="form-group">
                <label for="oauth2-id-security-policy-max-invalid-redeem-auth-code-attempts">Max. invalid redeem Auth Code exception attemps&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="in attemps"></span></label>
                <input class="form-control" type="number" min="3" step="1" id="oauth2-id-security-policy-max-invalid-redeem-auth-code-attempts" name="oauth2-id-security-policy-max-invalid-redeem-auth-code-attempts" value="{{$config_values['OAuth2SecurityPolicy.MaxInvalidRedeemAuthCodeAttempts']}}"/>
            </div>
            <div class="form-group">
                <label for="oauth2-id-security-policy-max-invalid-client-credentials-attempts">Max. invalid client credentials exception attemps&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="in attemps"></span></label>
                <input class="form-control" type="number" min="3" step="1" id="oauth2-id-security-policy-max-invalid-client-credentials-attempts" name="oauth2-id-security-policy-max-invalid-client-credentials-attempts" value="{{$config_values['OAuth2SecurityPolicy.MaxInvalidClientCredentialsAttempts']}}"/>
            </div>

            <button type="submit" class="btn btn-default btn-md active">Submit</button>
        </form>
    </div>
</div>

@foreach($errors->all() as $message)
<div class="alert alert-danger alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    {{ $message }}
</div>
@endforeach

@stop

@section('scripts')
{{ HTML::script('assets/js/admin/server-config.js') }}
@append