@extends('layout')

@section('title')
<title>Welcome to OpenStackId - OpenStack ID Account Settings</title>
@stop

@section('content')

@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))

<div class="col-md-9" id="sidebar">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    Hello, {{{ $username }}}.
                    <div>Your OPENID: <a href="{{ str_replace("%23","#",$openid_url) }}">{{ str_replace("%23","#",$openid_url) }}</a></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    {{ Form::open(array('url' => URL::action('UserController@postUserProfileOptions'), 'method' => 'post')) }}
                    <legend><span class="glyphicon glyphicon-info-sign pointable" aria-hidden="true" title="this information will be public on your profile page"></span>&nbsp;OpenStack ID Account Settings:</legend>
                    <div class="checkbox">
                        <label class="checkbox">
                            {{ Form::checkbox('show_full_name', '1', $show_full_name) }}Show Full Name
                        </label>
                        </div>
                    <div class="checkbox">
                        <label class="checkbox">
                            {{ Form::checkbox('show_email', '1', $show_email) }}Show Email
                        </label>
                        </div>
                    <div class="checkbox">
                        <label class="checkbox">
                            {{ Form::checkbox('show_pic', '1', $show_pic) }}Show Photo
                        </label>
                        </div>
                        <div class="pull-right">
                            {{ Form::submit('Save',array('id'=>'save','class'=>'btn btn-default btn-md active')) }}
                        </div>

                    {{ Form::close() }}
                </div>
            </div>

            @if (count($sites)>0)
            <div class="row">
                <div id="trusted_sites" class="col-md-12">
                    <legend><span class="glyphicon glyphicon-info-sign pointable" aria-hidden="true" title="Users can keep track of their trusted sites and manage them"></span>&nbsp;Trusted Sites</legend>
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
                            <td>{{ HTML::link(URL::action("UserController@deleteTrustedSite",array("id"=>$site->id)),'Delete',array('class'=>'btn btn-default btn-md active btn-delete del-realm','title'=>'Deletes a decision about a particular trusted site,')) }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @if (count($actions)>0)
            <div class="row">
                <div id="actions" class="col-md-12">
                    <legend><span class="glyphicon glyphicon-info-sign pointable" aria-hidden="true" title="Users actions"></span>&nbsp;User Actions</legend>
                    <table class="table table-hover table-condensed">
                        <thead>
                        <tr>
                            <th>From Realm</th>
                            <th>Action</th>
                            <th>From IP</th>
                            <th><span class="glyphicon glyphicon-info-sign pointable" aria-hidden="true"title="Time is on UTC"></span>&nbsp;When</th>
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
<div class="col-md-3">
    &nbsp;
</div>
@stop
@section('scripts')
<script type="application/javascript">
    $(document).ready(function() {
        $('#profile','#main-menu').addClass('active');
    });
</script>
@stop