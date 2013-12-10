@extends('layout')

@section('title')
<title>Welcome to openstackId - Edit Profile</title>
@stop

@section('content')
<div class="span7" id="sidebar">

    <div class="row-fluid">
        <div class="span12">
            Hello, {{{ $username }}}.
            <a class="btn btn-small" href="{{ URL::action("UserController@logout") }}"">logout</a>
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
                        <td>{{ HTML::link(URL::action("UserController@get_deleteTrustedSite",array("id"=>$site->id)),'Delete',array('class'=>'btn del-realm','title'=>'Deletes a decision about a particular trusted site,')) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if (count($clients)>0)
    <div class="row-fluid">
        <div id="clients" class="span12">
            <legend><i class="icon-info-sign accordion-toggle" title="Users can keep track of their registered applications and manage them"></i>&nbsp;Registered Applications</legend>
            {{ HTML::link(URL::action("UserController@postAddRegisteredClient",null),'Add',array('class'=>'btn add-client','title'=>'Adds a Registered Application')) }}
            <table class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th>Application Name</th>
                    <th>Client Id</th>
                    <th>Client Secret</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($clients as $client)
                    <tr>
                        <td>{{ $client->app_name }}</td>
                        <td>{{ $client->client_id}}</td>
                        <td>{{ $client->client_secret }}</td>
                        <td>&nbsp;
                            {{ HTML::link(URL::action("UserController@getEditRegisteredClient",array("id"=>$client->id)),'Edit',array('class'=>'btn edit-client','title'=>'Edits a Registered Application')) }}
                            {{ HTML::link(URL::action("UserController@getDeleteRegisteredClient",array("id"=>$client->id)),'Delete',array('class'=>'btn del-client','title'=>'Deletes a Registered Application')) }}</td>
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
<div class="span5">
</div>

@stop

@section('scripts')
<script type="application/javascript">
    $(document).ready(function() {

        $("body").on('click',".del-client",function(event){
            var link = $(this).attr('href');
            $.ajax(
                {
                    type: "GET",
                    url: link,
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...

                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert( "Request failed: " + textStatus );
                    }
                }
            );
            event.preventDefault();
            return false;
        });

        $("body").on('click',".add-client",function(event){
            var link = $(this).attr('href');
            event.preventDefault();
            return false;
        });
    });
</script>
@stop

