@extends('layout')
@section('title')
<title>Welcome to openstackId - My Account</title>
@stop
@section('content')

<div class="navbar">
    <div class="navbar-inner">
        <ul class="nav">
            <li><a href='{{ URL::action("UserController@getProfile") }}'>Profile</a></li>
            <li class="dropdown active">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    OAUTH2 Console
                    <b class="caret"></b>
                </a>
                <ul class="dropdown-menu">
                    <li><a href='{{URL::action("AdminController@listOAuth2Clients")}}'>OAUTH2 Applications</a></li>
                    <li><a href='{{URL::action("AdminController@editIssuedGrants")}}'>Issued OAUTH2 Grants</a></li>
                </ul>
            </li>
            @if($is_server_admin)
            <li><a href='{{URL::action("AdminController@listResourceServers")}}'>Server Administration</a></li>
            @endif
            <li><a href='{{ URL::action("UserController@logout") }}'>Logout</a></li>
        </ul>
    </div>
</div>

<legend>Authorized Access to your OpenstackId Account</legend>
<h2>Connected Sites, Apps, and Services</h2>
<p>
    You have granted the following services access to your OpenstackId Account: <br>
</p>
<h4>Online Access&nbsp;<i class="icon-info-sign accordion-toggle" title=""></i></h4>

@if(count($access_tokens))
<table class="table table-hover table-condensed">
    <thead>
    <tr>
        <th>Application Type</th>
        <th>Application Name</th>
        <th>Granted Scopes</th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    @foreach($access_tokens as $access_token)
    <tr>
        <td>{{$access_token->client()->first()->getFriendlyApplicationType()}}</td>
        <td>{{$access_token->client()->first()->app_name}}</td>
        <td>{{$access_token->getFriendlyScopes()}}</td>
        <td>{{ HTML::link(URL::action("AdminController@revokeToken",array("value"=>$access_token->value, "hint"=>'access_token')),'Revoke Access',array('class'=>'btn revoke-access','title'=>'Revoke Access Token')) }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
@else
<p>
    * There are not currently access tokens issued for this user.
</p>
@endif

<h4>Offline Access&nbsp;<i class="icon-info-sign accordion-toggle" title="In some cases, your application may need to access an API when the user is not present. Examples of this include backup services and applications that make blogger posts exactly at 8am on Monday morning. This style of access is called offline, and web server applications may request offline access from a user. The normal and default style of access is called online. "></i></h4>
@if(count($refresh_tokens))
<table class="table table-hover table-condensed">
        <thead>
        <tr>
            <th>Application Type</th>
            <th>Application Name</th>
            <th>Granted Scopes</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        @foreach($refresh_tokens as $refresh_token)
        <tr>
            <td>{{$refresh_token->client()->first()->getFriendlyApplicationType()}}</td>
            <td>{{$refresh_token->client()->first()->app_name}}</td>
            <td>{{$refresh_token->getFriendlyScopes()}}</td>
            <td>{{ HTML::link(URL::action("AdminController@revokeToken",array("value"=>$refresh_token->value, "hint"=>'refresh_token')),'Revoke Access',array('class'=>'btn revoke-access','title'=>'Revoke Access Token')) }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
@else
<p>
    * There are not currently refresh tokens issued for this user.
</p>
@endif
@stop
@section('scripts')
<script type="application/javascript">
    $(document).ready(function() {

        $("body").on('click',".revoke-access",function(event){
            if(!confirm("Are you sure to revoke this grant?")){
                event.preventDefault();
                return false;
            }
            return true;
        });
    });
</script>
@stop