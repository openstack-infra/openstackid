@extends('layout')
@section('title')
<title>Welcome to openstackId - Edit Registered Application</title>
@stop
@section('content')

<div class="navbar">
    <div class="navbar-inner">
        <ul class="nav">
            <li ><a href='{{ URL::action("UserController@getProfile") }}'>Profile</a></li>
            <li class="active"><a href='{{URL::action("AdminController@listOAuth2Clients")}}'>OAUTH2 Console</a></li>
            <li><a href='{{URL::action("AdminController@editIssuedGrants")}}'>Issued OAUTH2 Grants</a></li>
            @if($is_server_admin)
            <li><a href='{{URL::action("AdminController@listResourceServers")}}'>Server Administration</a></li>
            @endif
            <li><a href='{{ URL::action("UserController@logout") }}'>Logout</a></li>
        </ul>
    </div>
</div>

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