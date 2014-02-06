@extends('layout')
@section('title')
<title>Welcome to openstackId - OAUTH2 Console - Edit Client</title>
@stop
@section('content')
@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
<legend>Client {{ $client->app_name }}</legend>
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
<div class="accordion" id="accordion2">
    <div class="accordion-group">
        <div class="accordion-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
                OAuth 2.0 Client ID
            </a>
        </div>
        <div id="collapseOne" class="accordion-body collapse in">
            <div class="accordion-inner">
                @include('oauth2.profile.edit-client-data',array('access_tokens' => $access_tokens, 'refresh_tokens' => $refresh_tokens,'client'=>$client,'allowed_uris'=>$allowed_uris))
            </div>
        </div>
    </div>
    <div class="accordion-group">
        <div class="accordion-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">
                Authorized Redirection Uris
            </a>
        </div>
        <div id="collapseTwo" class="accordion-body collapse">
            <div class="accordion-inner">
                @include('oauth2.profile.edit-client-redirect-uris',array('access_tokens' => $access_tokens, 'refresh_tokens' => $refresh_tokens,'client'=>$client,'allowed_uris'=>$allowed_uris))
            </div>
        </div>
    </div>
    @if($client->application_type == oauth2\models\IClient::ApplicationType_JS_Client)
    <div class="accordion-group">
        <div class="accordion-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOrigins">
                Allowed Javascript Origins
            </a>
        </div>
        <div id="collapseOrigins" class="accordion-body collapse">
            <div class="accordion-inner">
                @include('oauth2.profile.edit-client-allowed-origins',array('access_tokens' => $access_tokens, 'refresh_tokens' => $refresh_tokens,'client'=>$client,'allowed_uris'=>$allowed_uris,'allowed_origins'=>$allowed_origins))
            </div>
        </div>
    </div>
    @endif
    <div class="accordion-group">
        <div class="accordion-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseThree">
                Application Allowed Scopes
            </a>
        </div>
        <div id="collapseThree" class="accordion-body collapse">
            <div class="accordion-inner">
                @include('oauth2.profile.edit-client-scopes',array('access_tokens' => $access_tokens, 'refresh_tokens' => $refresh_tokens,'client'=>$client) )
            </div>
        </div>
    </div>
    <div class="accordion-group">
        <div class="accordion-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseFour">
                Application Grants
            </a>
        </div>
        <div id="collapseFour" class="accordion-body collapse">
            <div class="accordion-inner">
                @include('oauth2.profile.edit-client-tokens',array('access_tokens' => $access_tokens, 'refresh_tokens' => $refresh_tokens,'client'=>$client) )
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