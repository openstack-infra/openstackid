@extends('layout')
@section('title')
<title>Welcome to openstackId - OAUTH2 Console - Clients</title>
@stop
@section('content')
@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
<div class="row-fluid">
    <div id="clients" class="span12">
        <legend><i class="icon-info-sign accordion-toggle" title="Users can keep track of their registered applications and manage them"></i>&nbsp;Registered Applications</legend>
        {{ HTML::link(URL::action("ClientApiController@create",null),'Register Application',array('class'=>'btn add-client','title'=>'Adds a Registered Application')) }}
        @if (count($clients)>0)
        <table id='tclients' class="table table-hover table-condensed">
            <thead>
            <tr>
                <th>Application Name</th>
                <th>Application Type</th>
                <th>Is Active</th>
                <th>Is Locked</th>
                <th>Modified</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody id="body-registered-clients">
            @foreach ($clients as $client)
            <tr>
                <td>{{ $client->app_name }}</td>
                <td>{{ $client->getFriendlyApplicationType()}}</td>
                <td>
                    <input type="checkbox" class="app-active-checkbox" id="app-active_{{$client->id}}"
                    @if ( $client->active)
                    checked
                    @endif
                    value="{{$client->id}}"/>
                </td>
                <td>
                    <input type="checkbox" class="app-locked-checkbox" id="app-locked_{{$client->id}}"
                    @if ( $client->locked)
                    checked
                    @endif
                    value="{{$client->id}}" disabled="disabled" />
                </td>
                <td>{{ $client->updated_at }}</td>
                <td>&nbsp;
                    {{ HTML::link(URL::action("AdminController@editRegisteredClient",array("id"=>$client->id)),'Edit',array('class'=>'btn edit-client','title'=>'Edits a Registered Application')) }}
                    {{ HTML::link(URL::action("ClientApiController@delete",array("id"=>$client->id)),'Delete',array('class'=>'btn del-client','title'=>'Deletes a Registered Application')) }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

<div id="dialog-form-application" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Register new Application</h3>
    </div>
    <div class="modal-body">
        <p style="font-size: 10px;"><i class="icon-info-sign accordion-toggle" title="OAuth 2.0 allows users to share specific data with you (for example, contact lists) while keeping their usernames, passwords, and other information private."></i> You need to register your application to get the necessary credentials to call a Openstack API</p>
        <form id="form-application" name="form-application" class="form-horizontal">
            <fieldset>
                <div class="control-group">
                <label class="control-label" for="app_name">Application Name</label>
                <div class="controls">
                    <input type="text" name="app_name" id="app_name">
                </div>
                </div>

                <div class="control-group">
                <label  class="control-label" for="website">Application Web Site Url</label>
                <div class="controls">
                    <input type="text" name="website" id="website">
                </div>
                </div>

                <div class="control-group">
                    <label class="control-label"  for="app_description">Application Description</label>
                    <div class="controls">
                        <textarea style="resize: none;" rows="4" cols="50" name="app_description" id="app_description"></textarea>
                    </div>
                </div>

                <div class="control-group">

                    <label class="control-label" for="application_type">Application Type</label>
                    <i class="icon-info-sign accordion-toggle" style="float:left;" title="Web Server Application : The OpenstackId OAuth 2.0 endpoint supports web server applications that use languages and frameworks such as PHP, Java, Python, Ruby, and ASP.NET. These applications might access an Openstack API while the user is present at the application or after the user has left the application. This flow requires that the application can keep a secret.
Client Side (JS) : JavaScript-centric applications. These applications may access a Openstack API while the user is present at the application, and this type of application cannot keep a secret.
Service Account : The OpenstackId OAuth 2.0 Authorization Server supports server-to-server interactions. The requesting application has to prove its own identity to gain access to an API, and an end-user doesn't have to be involved. "></i>
                    <div class="controls">

                        <select id="application_type" name="application_type">
                            <option value="WEB_APPLICATION">Web Server Application</option>
                            <option value="JS_CLIENT">Client Side (JS)</option>
                            <option value="SERVICE">Service Account</option>
                        </select>

                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <label class="checkbox">
                            <input type="checkbox" id="active" name="active">&nbsp;Active
                        </label>
                    </div>
                </div>

            </fieldset>
        </form>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <button id='save-application' class="btn btn-primary">Save changes</button>
    </div>
</div>
@stop

@section('scripts')
<script type="application/javascript">
	var userId = {{$user_id}};
	var clientsUrls = {
		load:'{{ URL::action("ClientApiController@getByPage",array("offset"=>1,"limit"=>1000,"user_id"=>$user_id ))}}',
		edit:'{{ URL::action("AdminController@editRegisteredClient",array("id"=>"@id")) }}',
		delete:'{{ URL::action("ClientApiController@delete",array("id"=>"@id")) }}',
		add: '{{URL::action("ClientApiController@create",null)}}',
		activate : '{{ URL::action("ClientApiController@activate",array("id"=>"@id")) }}',
		deactivate:'{{ URL::action("ClientApiController@deactivate",array("id"=>"@id")) }}'
	};
</script>
{{ HTML::script('js/oauth2/profile/clients.js') }}
@stop