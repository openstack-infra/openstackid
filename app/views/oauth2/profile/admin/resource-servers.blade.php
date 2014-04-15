@extends('layout')

@section('title')
<title>Welcome to openstackId - Server Admin - Resource Server</title>
@stop

@section('content')
@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
<div class="row-fluid">

    <div class="row-fluid">
        <h4 style="float:left"><i class="icon-info-sign accordion-toggle" title=""></i>&nbsp;Resource Servers</h4>
        <div style="position: relative;float:left;">
            <div style="position:absolute;top:13px;margin-left:5px"><i class="icon-refresh accordion-toggle refresh-servers" title="Update Resource Server List"></i></div>
        </div>
    </div>
    <div class="row-fluid">
        <div class="alert alert-info" id="info-resource-servers" style="display: none">
            <strong>There are not any available Scopes</strong>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span12">
            {{ HTML::link(URL::action("ApiResourceServerController@create"),'Add Resource Server',array('class'=>'btn add-resource-server','title'=>'Adds a New Resource Server')) }}
        </div>
    </div>
    <table id='table-resource-servers' class="table table-hover table-condensed">
        <thead>
        <tr>
            <th>Friendly Name</th>
            <th>Host</th>
            <th>IP Address</th>
            <th>Active</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody id="body-resource-servers">
        @foreach ($resource_servers as $resource_server)
        <tr id="{{ $resource_server->id }}">
            <td>{{$resource_server->friendly_name}}</td>
            <td>{{$resource_server->host}}</td>
            <td>{{$resource_server->ip}}</td>
            <td>
                <input type="checkbox" class="resource-server-active-checkbox" id="resource-server-active_{{$resource_server->id}}"
                       data-resource-server-id="{{$resource_server->id}}"
                @if ( $resource_server->active)
                checked
                @endif
                value="{{$resource_server->id}}"/>
            </td>
            <td>
                &nbsp;
                {{ HTML::link(URL::action("AdminController@editResourceServer",array("id"=>$resource_server->id)),'Edit',array('class'=>'btn edit-resource-server','title'=>'Edits a Registered Resource Server')) }}
                {{ HTML::link(URL::action("ApiResourceServerController@delete",array("id"=>$resource_server->id)),'Delete',array('class'=>'btn delete-resource-server','title'=>'Deletes a Registered Resource Server')) }}
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div id="dialog-form-resource-server" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Register New Resource Server</h3>
    </div>
    <div class="modal-body">
        <form id="form-resource-server" name="form-resource-server">
            <fieldset>
                <label for="name">Host</label>
                <input type="text" name="host" id="host">
                <label for="friendly_name">Friendly Name</label>
                <input type="text" name="friendly_name" id="friendly_name">
                <label for="ip">IP Address</label>
                <input type="text" name="ip" id="ip">
                <label class="checkbox">
                    <input type="checkbox" id="active" name="active">&nbsp;Active
                </label>
            </fieldset>
        </form>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <button id='save-resource-server' class="btn btn-primary">Save changes</button>
    </div>
</div>

@stop

@section('scripts')
<script type="application/javascript">
	var resourceServerUrls = {
		get : '{{URL::action("ApiResourceServerController@getByPage",array("offset"=>1,"limit"=>1000))}}',
		edit : '{{ URL::action("AdminController@editResourceServer",array("id"=>-1)) }}',
		delete : '{{ URL::action("ApiResourceServerController@delete",array("id"=>-1)) }}',
		activate : '{{ URL::action("ApiResourceServerController@activate",array("id"=>"@id")) }}',
		deactivate : '{{ URL::action("ApiResourceServerController@deactivate",array("id"=>"@id")) }}',
		add : '{{URL::action("ApiResourceServerController@create",null)}}'
	};
</script>
{{ HTML::script('js/oauth2/profile/admin/resource-servers.js') }}
@stop