@extends('layout')

@section('title')
<title>Welcome to OpenStackId - OAUTH2 Console - Edit Client</title>
@stop
@section('css')
    {{ HTML::style('bower_assets/bootstrap-tagsinput/dist/bootstrap-tagsinput.css') }}
    {{ HTML::style('bower_assets/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}
    {{ HTML::style('bower_assets/bootstrap-tagsinput/dist/bootstrap-tagsinput-typeahead.css') }}
    {{ HTML::style('assets/css/edit-client.css') }}
@append
@section('scripts')
    {{ HTML::script('bower_assets/typeahead.js/dist/typeahead.bundle.js')}}
    {{ HTML::script('bower_assets/bootstrap-tagsinput/dist/bootstrap-tagsinput.js')}}
    {{ HTML::script('bower_assets/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}
    <script type="application/javascript">

        var dataClientUrls =
        {
            refresh: '{{URL::action("ClientApiController@setRefreshTokenClient",array("id"=>$client->id))}}',
            rotate: '{{URL::action("ClientApiController@setRotateRefreshTokenPolicy",array("id"=>$client->id))}}',
            update: '{{URL::action("ClientApiController@update")}}',
            add_public_key: '{{URL::action("ClientPublicKeyApiController@create",array("id"=>$client->id))}}',
            get_public_keys: '{{URL::action("ClientPublicKeyApiController@getByPage",array("id"=>$client->id))}}',
            delete_public_key: '{{URL::action("ClientPublicKeyApiController@delete",array("id" => $client->id, 'public_key_id'=> '@public_key_id'))}}',
            update_public_key: '{{URL::action("ClientPublicKeyApiController@update",array("id" => $client->id, 'public_key_id'=> '@public_key_id'))}}',
            fetchUsers: '{{URL::action("UserApiController@fetch",null)}}',
        };

        var oauth2_supported_algorithms =
        {
            sig_algorihtms:
            {
                mac:{{utils\ArrayUtils::toJson(oauth2\OAuth2Protocol::$supported_signing_algorithms_hmac_sha2)}},
                rsa:{{utils\ArrayUtils::toJson(oauth2\OAuth2Protocol::$supported_signing_algorithms_rsa)}}
            },
            key_management_algorihtms: {{utils\ArrayUtils::toJson(oauth2\OAuth2Protocol::$supported_key_management_algorithms)}},
            content_encryption_algorihtms:  {{utils\ArrayUtils::toJson(oauth2\OAuth2Protocol::$supported_content_encryption_algorithms)}}
        };
        var current_admin_users  = [];

        @foreach($client->admin_users()->get() as $user)
        current_admin_users.push({ "id": {{$user->id}} , "value": "{{ $user->getFullName() }}" });
        @endforeach

        $(document).ready
        (
                function()
                {
                }
        );
    </script>
@append
@section('content')
@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
<legend>
    <span aria-hidden="true" class="glyphicon glyphicon-info-sign pointable"
          title="OAuth 2.0 allows users to share specific data with you (for example, contact lists) while keeping their usernames, passwords, and other information private.">

    </span>&nbsp;{{$client->getFriendlyApplicationType()}} - Client # {{ $client->id }}
</legend>
<div class="row">
    <div style="padding-left:15px" class="col-md-2 clear-padding"><strong>Created By:&nbsp;</strong></div><div class="col-md-10 clear-padding">{{ $client->getOwnerNice() }}</div>
</div>
<div class="row">
    <div style="padding-left:15px" class="col-md-2 clear-padding"><strong>Edited By</strong>:&nbsp;</div><div class="col-md-10 clear-padding">{{ $client->getEditedByNice() }}</div>
</div>
@if($errors->any())
<div class="errors">
    <ul>
        @foreach($errors->all() as $error)
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            {{ $error }}
        </div>
        @endforeach
    </ul>
</div>
@endif
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
    <!-- main data -->
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
                @include('oauth2.profile.edit-client-data',array('access_tokens' => $access_tokens, 'refresh_tokens' => $refresh_tokens,'client' => $client))
            </div>
        </div>
    </div>
    <!-- scopes -->
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
    <!-- grants -->
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
    <!-- security settings -->
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