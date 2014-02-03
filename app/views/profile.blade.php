@extends('layout')

@section('title')
<title>Welcome to openstackId - Edit Profile</title>
@stop

@section('content')

<div class="navbar">
    <div class="navbar-inner">
        <ul class="nav">
            <li class="active"><a href='{{ URL::action("UserController@getProfile") }}'>Profile</a></li>
            <li><a href=''></a></li>

            <li class="dropdown">
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

<div class="span9" id="sidebar">
    <div class="row-fluid">
        <div class="span12">
            <div class="row-fluid">
                <div class="span12">
                    Hello, {{{ $username }}}.
                    <div>Your OPENID: <a href="{{ str_replace("%23","#",$openid_url) }}">{{ str_replace("%23","#",$openid_url) }}</a></div>
                </div>
            </div>

            <div class="row-fluid">
                <div class="span12">
                    {{ Form::open(array('url' => URL::action('UserController@postUserProfileOptions'), 'method' => 'post')) }}
                    <legend><i class="icon-info-sign accordion-toggle" title="this information will be public on your profile page"></i>&nbsp;Edit your profile options:</legend>
                    <fieldset>
                        <label class="checkbox">
                            {{ Form::checkbox('show_full_name', '1', $show_full_name) }}Show Full Name
                        </label>
                        <label class="checkbox">
                            {{ Form::checkbox('show_email', '1', $show_email) }}Show Email
                        </label>
                        <label class="checkbox">
                            {{ Form::checkbox('show_pic', '1', $show_pic) }}Show Photo
                        </label>
                        <div class="pull-right">
                            {{ Form::submit('Save',array('id'=>'save','class'=>'btn')) }}
                        </div>
                    </fieldset>
                    {{ Form::close() }}
                </div>
            </div>

            @if (count($sites)>0)
            <div class="row-fluid">
                <div id="trusted_sites" class="span12">
                    <legend><i class="icon-info-sign accordion-toggle" title="Users can keep track of their trusted sites and manage them"></i>&nbsp;Trusted Sites</legend>
                    <table class="table table-hover table-condensed">
                        <thead>
                        <tr>
                            <th>Realm</th>
                            <th>Policy</th>
                            <th>Trusted Data</th>
                            <th>&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($sites as $site)
                        @if($site->getAuthorizationPolicy()=='AllowForever')
                        <tr class="success">
                            @else
                        <tr class="error">
                            @endif
                            <td>{{ $site->getRealm() }}</td>
                            <td>{{ $site->getAuthorizationPolicy()}}</td>
                            <td>{{ $site->getUITrustedData() }}</td>
                            <td>{{ HTML::link(URL::action("UserController@deleteTrustedSite",array("id"=>$site->id)),'Delete',array('class'=>'btn del-realm','title'=>'Deletes a decision about a particular trusted site,')) }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @if (count($actions)>0)
            <div class="row-fluid">
                <div id="actions" class="span12">
                    <legend><i class="icon-info-sign accordion-toggle" title="Users actions"></i>&nbsp;User Actions</legend>
                    <table class="table table-hover table-condensed">
                        <thead>
                        <tr>
                            <th>From Realm</th>
                            <th>Action</th>
                            <th>From IP</th>
                            <th><i class="icon-info-sign accordion-toggle" title="Time is on UTC"></i>&nbsp;When</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($actions as $action)
                        <tr>
                            @if(is_null($action->realm))
                            <td>Site</td>
                            @else
                            <td>{{ $action->realm }}</td>
                            @endif
                            <td>{{ $action->user_action }}</td>
                            <td>{{ $action->from_ip }}</td>
                            <td>{{ $action->created_at }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
<div class="span3">
    &nbsp;
</div>
@stop

@section('scripts')
<script type="application/javascript">
    $(document).ready(function() {

    });
</script>
@stop

