@extends('layout')
@section('title')
<title>Welcome to openstackId - My Account</title>
@stop
@section('content')
@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))

<legend>Authorized Access to your OpenstackId Account</legend>
<h2>Connected Sites, Apps, and Services</h2>
<p>
    You have granted the following services access to your OpenstackId Account: <br>
</p>
<h4>Online Access&nbsp;<i class="icon-info-sign accordion-toggle" title=""></i></h4>
<table id="table-access-tokens" class="table table-hover table-condensed">
    <thead>
    <tr>
        <th>Application Type</th>
        <th>Application Name</th>
        <th>Granted Scopes</th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody id="body-access-tokens">
    @foreach($access_tokens as $access_token)
    <tr id="{{$access_token->value}}">
        <td>{{$access_token->client()->first()->getFriendlyApplicationType()}}</td>
        <td>{{$access_token->client()->first()->app_name}}</td>
        <td>{{$access_token->getFriendlyScopes()}}</td>
        <td>{{ HTML::link(URL::action("UserApiController@revokeToken",array("id"=>$user_id,"value"=>$access_token->value, "hint"=>'access_token')),'Revoke Access',array('data-value' => $access_token->value,'data-hint'=>'access_token','class'=>'btn revoke-access','title'=>'Revoke Access Token')) }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
<span id="info-access-tokens" class="label label-info">** There are not currently access tokens issued for this user.</span>
<h4>Offline Access&nbsp;<i class="icon-info-sign accordion-toggle" title="In some cases, your application may need to access an API when the user is not present. Examples of this include backup services and applications that make blogger posts exactly at 8am on Monday morning. This style of access is called offline, and web server applications may request offline access from a user. The normal and default style of access is called online. "></i></h4>
<table id="table-refresh-tokens" class="table table-hover table-condensed">
        <thead>
        <tr>
            <th>Application Type</th>
            <th>Application Name</th>
            <th>Granted Scopes</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody id="body-refresh-tokens">
        @foreach($refresh_tokens as $refresh_token)
        <tr id="{{$refresh_token->value}}">
            <td>{{$refresh_token->client()->first()->getFriendlyApplicationType()}}</td>
            <td>{{$refresh_token->client()->first()->app_name}}</td>
            <td>{{$refresh_token->getFriendlyScopes()}}</td>
            <td>{{ HTML::link(URL::action("UserApiController@revokeToken",array("id" => $user_id,"value" => $refresh_token->value, "hint" => 'refresh_token')),'Revoke Access',array('data-value' => $refresh_token->value,'data-hint' => 'refresh_token','class' => 'btn revoke-access','title' => 'Revoke Access Token')) }}</td>
        </tr>
        @endforeach
        </tbody>
</table>
<span id="info-refresh-tokens" class="label label-info">** There are not currently refresh tokens issued for this user.</span>
@stop
@section('scripts')
<script type="application/javascript">
    $(document).ready(function() {

        $('#oauth2-console','#main-menu').addClass('active');

        if($('#table-access-tokens tr').length===1){
            $('#info-access-tokens').show();
            $('#table-access-tokens').hide();
        }
        else{
            $('#info-access-tokens').hide();
            $('#table-access-tokens').show();
        }

        if($('#table-refresh-tokens tr').length===1){
            $('#info-refresh-tokens').show();
            $('#table-refresh-tokens').hide();
        }
        else{
            $('#info-refresh-tokens').hide();
            $('#table-refresh-tokens').show();
        }

        $("body").on('click',".revoke-access",function(event){
            if(confirm("Are you sure to revoke this grant?")){
                var url   = $(this).attr('href');
                var value = $(this).attr('data-value');
                var hint  = $(this).attr('data-hint');
                var body  = hint=='access_token'?'body-access-tokens':'body-refresh-tokens'
                var table = hint=='access_token'?'table-access-tokens':'table-refresh-tokens'
                var info  = hint=='access_token'?'info-access-tokens':'info-refresh-tokens'
                $.ajax(
                    {
                        type: "DELETE",
                        url: url,
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            //load data...
                            $('#'+value,'#'+body).remove();

                            if($('#'+table+' tr').length===1){
                                $('#'+info).show();
                                $('#'+table).hide();
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            ajaxError(jqXHR, textStatus, errorThrown);
                        }
                    }
                );
            }
            event.preventDefault();
            return false;
        });
    });
</script>
@stop