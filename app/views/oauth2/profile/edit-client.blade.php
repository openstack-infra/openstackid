@extends('layout')
@section('title')
<title>Welcome to openstackId - OAUTH2 Console - Edit Client</title>
@stop
@section('content')
@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
<legend><i class="icon-info-sign accordion-toggle" title="OAuth 2.0 allows users to share specific data with you (for example, contact lists) while keeping their usernames, passwords, and other information private."></i>&nbsp;{{$client->getFriendlyApplicationType()}} - Client {{ $client->app_name }}</legend>
@if($errors->any())
<div class="errors">
    <ul>
        @foreach($errors->all() as $error)
        <div class="alert alert-error">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ $error }}
        </div>
        @endforeach
    </ul>
</div>
@endif
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
    <div class="panel panel-default" style="padding-bottom:0px">
        <div class="panel-heading" role="tab" id="headingOne" style="margin-bottom:0px">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    OAuth 2.0 Client Data
                </a>
            </h4>
        </div>
        <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
            <div class="panel-body">
                @include('oauth2.profile.edit-client-data',array('access_tokens' => $access_tokens, 'refresh_tokens' => $refresh_tokens,'client'=>$client,'allowed_uris'=>$allowed_uris))
            </div>
        </div>
    </div>
    <div class="panel panel-default" style="padding-bottom:0px">
        <div class="panel-heading" role="tab" id="headingTwo" style="margin-bottom:0px">
            <h4 class="panel-title">
                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    Authorized Redirection Uris
                </a>
            </h4>
        </div>
        <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
            <div class="panel-body">
                @include('oauth2.profile.edit-client-redirect-uris',array('access_tokens' => $access_tokens, 'refresh_tokens' => $refresh_tokens,'client'=>$client,'allowed_uris'=>$allowed_uris))
            </div>
        </div>
    </div>
    @if($client->application_type == oauth2\models\IClient::ApplicationType_JS_Client)
    <div class="panel panel-default" style="padding-bottom:0px">
        <div class="panel-heading" role="tab" id="headingThree" style="margin-bottom:0px">
            <h4 class="panel-title">
                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    Allowed Javascript Origins
                </a>
            </h4>
        </div>
        <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
            <div class="panel-body">
                @include('oauth2.profile.edit-client-allowed-origins',array('access_tokens' => $access_tokens, 'refresh_tokens' => $refresh_tokens,'client'=>$client,'allowed_uris'=>$allowed_uris,'allowed_origins'=>$allowed_origins))
            </div>
        </div>
    </div>
    @endif
    <div class="panel panel-default" style="padding-bottom:0px">
        <div class="panel-heading" role="tab" id="heading4" style="margin-bottom:0px">
            <h4 class="panel-title">
                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse4" aria-expanded="false" aria-controls="collapse4">
                    Application Allowed Scopes
                </a>
            </h4>
        </div>
        <div id="collapse4" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading4">
            <div class="panel-body">
                @include('oauth2.profile.edit-client-scopes',array('access_tokens' => $access_tokens, 'refresh_tokens' => $refresh_tokens,'client'=>$client) )
            </div>
        </div>
    </div>
    <div class="panel panel-default" style="padding-bottom:0px">
        <div class="panel-heading" role="tab" id="heading5" style="margin-bottom:0px">
            <h4 class="panel-title">
                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse5" aria-expanded="false" aria-controls="collapse5">
                    Application Grants
                </a>
            </h4>
        </div>
        <div id="collapse5" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading5">
            <div class="panel-body">
                @include('oauth2.profile.edit-client-tokens',array('access_tokens' => $access_tokens, 'refresh_tokens' => $refresh_tokens,'client'=>$client) )
            </div>
        </div>
    </div>
    <div class="panel panel-default" style="padding-bottom:0px">
        <div class="panel-heading" role="tab" id="heading6" style="margin-bottom:0px">
            <h4 class="panel-title">
                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse6" aria-expanded="false" aria-controls="collapse6">
                    Security Settings
                </a>
            </h4>
        </div>
        <div id="collapse6" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading6">
            <div class="panel-body">
                @include('oauth2.profile.edit-client-security-main-settings',array('client' => $client) )
                <hr/>
                @include('oauth2.profile.edit-client-public-keys',array('client' => $client) )
                <hr/>
                @include('oauth2.profile.edit-client-security-logout',array('client' => $client) )
            </div>
        </div>
    </div>
</div>
@stop
@section('scripts')
<script type="application/javascript">
    $(document).ready(function() {

    });
</script>
@stop