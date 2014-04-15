@extends('layout')

@section('title')
<title>Welcome to openstackId - Server Admin - Edit Resource Server</title>
@stop

@section('content')
@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
<a href="{{ URL::action("AdminController@listResourceServers") }}">Go Back</a>
<legend>Edit Resource Server - Id {{ $resource_server->id }}</legend>
<div class="row-fluid">
    <div class="span6">
        <form class="form-horizontal" id="resource-server-form" name="resource-server-form" action='{{URL::action("ApiResourceServerController@update",null)}}'>
            <fieldset>
                <div class="control-group">
                    <label  class="control-label"  for="host">Host</label>
                    <div class="controls">
                        <input type="text" name="host" id="host" value="{{ $resource_server->host }}">
                    </div>
                </div>
                <div class="control-group">
                    <label  class="control-label" for="friendly_name">Friendly Name</label>
                    <div class="controls">
                        <input type="text" name="friendly_name" id="friendly_name" value="{{ $resource_server->friendly_name }}">
                    </div>
                </div>
                <div class="control-group">
                    <label  class="control-label"  for="ip">IP Address</label>
                    <div class="controls">
                         <input type="text" name="ip" id="ip" value="{{ $resource_server->ip }}">
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <label class="checkbox">
                            <input type="checkbox" id="active"
                            @if ( $resource_server->active)
                            checked
                            @endif
                            name="active">&nbsp;Active
                        </label>
                    </div>
                </div>
                @if(!is_null($resource_server->client()->first()))
                <div class="control-group">
                    <div class="controls">
                        <label for="client_id" class="label-client-secret">Client ID</label>
                        <span id="client_id">{{ $resource_server->client()->first()->client_id }}</span>
                        <label for="client_secret" class="label-client-secret">Client Secret</label>
                        <span id="client_secret">{{ $resource_server->client()->first()->client_secret }}</span>
                        {{ HTML::link(URL::action("ApiResourceServerController@regenerateClientSecret",array("id"=> $resource_server->id)),'Regenerate',array('class'=>'btn regenerate-client-secret','title'=>'Regenerates Client Secret')) }}
                    </div>
                </div>
                @endif
                <div class="control-group">
                    <div class="controls">
                        <button type="submit" class="btn">Save</button>
                    </div>
                </div>
                <input type="hidden" name="id" id="id" value="{{ $resource_server->id }}"/>
            </fieldset>
        </form>
    </div>
</div>

<div class="row-fluid">
    <div class="span12">
    <div class="row-fluid">
        <h4 style="float:left"><i class="icon-info-sign accordion-toggle" title=""></i>&nbsp;Available Apis</h4>
        <div style="position: relative;float:left;">
            <div style="position:absolute;top:13px;margin-left:5px"><i class="icon-refresh accordion-toggle refresh-apis" title="Update Apis List"></i></div>
        </div>
    </div>
    <div class="row-fluid">
        <div class="alert alert-info" id="info-apis" style="display: none">
            <strong>There are not any available APIS</strong>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span12">
            {{ HTML::link(URL::action("ApiController@create"),'Register API',array('class'=>'btn add-api','title'=>'Adds a New API')) }}
        </div>
    </div>
    <div class="row-fluid">
        <div class="span12">
    <table id='table-apis' class="table table-hover table-condensed">
        <thead>
        <tr>
            <th>&nbsp;</th>
            <th>Name</th>
            <th>Active</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody id="body-apis">
        @foreach($resource_server->apis()->get() as $api)
        <tr>
            <td><img src="{{ $api->getLogo()}}"  height="24" width="24" alt="{{ $api->name}} logo"/></td>
            <td>{{ $api->name}}</td>
            <td>
                <input type="checkbox" class="api-active-checkbox" data-api-id="{{$api->id}}" id="resource-server-api-active_{{$api->id}}"
                @if ( $api->active)
                checked
                @endif
                value="{{$api->id}}"/>
            </td>
            <td>
                &nbsp;
                {{ HTML::link(URL::action("AdminController@editApi",array("id"=>$api->id)),'Edit',array('class'=>'btn edit-api','title'=>'Edits a Registered Resource Server API')) }}
                {{ HTML::link(URL::action("ApiController@delete",array("id"=>$api->id)),'Delete',array('class'=>'btn delete-api','title'=>'Deletes a Registered Resource Server API'))}}
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
        </div>
    </div>
    </div>
</div>

<div id="dialog-form-api" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Register New Resource Server API</h3>
    </div>
    <div class="modal-body">
        <form id="form-api" name="form-api">
            <fieldset>
                <label for="name">Name</label>
                <input type="text" name="name" id="name">
                <label for="description">Description</label>
                <textarea style="resize: none;" rows="4" cols="50" name="description" id="description"></textarea>
                <label class="checkbox">
                    <input type="checkbox" id="active" name="active">&nbsp;Active
                </label>
            </fieldset>
        </form>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <button id='save-api' class="btn btn-primary">Save changes</button>
    </div>
</div>

@stop

@section('scripts')
<script type="application/javascript">

    var resource_server_id = {{ $resource_server->id}};

	var ApiUrls = {
		get : '{{ URL::action("ApiController@getByPage",array("offset"=>1,"limit"=>1000,"resource_server_id"=>$resource_server->id)) }}',
		edit : '{{ URL::action("AdminController@editApi",array("id"=>-1)) }}',
		delete : '{{ URL::action("ApiController@delete",array("id"=>-1)) }}',
		add : '{{URL::action("ApiController@create",null)}}',
		activate: '{{ URL::action("ApiController@activate",array("id"=>"@id")) }}',
		deactivate: '{{ URL::action("ApiController@deactivate",array("id"=>"@id")) }}'
	};

	var resourceServerMessages = {
		success : '{{ Lang::get("messages.global_successfull_save_entity", array("entity" => "Resource Server")) }}'
	};
</script>
{{ HTML::script('js/oauth2/profile/admin/edit-resource-server.js') }}
@stop