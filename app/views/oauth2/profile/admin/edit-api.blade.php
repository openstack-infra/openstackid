@extends('layout')

@section('title')
<title>Welcome to openstackId - Server Admin - Edit API</title>
@stop

@section('content')
@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
<a href='{{ URL::action("AdminController@editResourceServer",array("id"=>$api->resource_server_id)) }}'>{{ Lang::get("messages.edit_api_go_back") }}</a>
<legend>{{ Lang::get("messages.edit_api_title", array("id" => $api->id)) }}</legend>
<div class="row-fluid">
    <div class="span6">
        <form class="form-horizontal" id="api-form" name="api-form" action='{{URL::action("ApiController@update",null)}}'>
            <fieldset>
                <div class="control-group">
                    <label  class="control-label" for="name">{{ Lang::get("messages.edit_api_form_name") }}</label>
                    <div class="controls">
                        <input type="text" name="name" id="name" value="{{ $api->name }}">
                    </div>
                </div>
                <div class="control-group">
                    <label  class="control-label" for="description">{{ Lang::get("messages.edit_api_form_description") }}</label>
                    <div class="controls">
                        <textarea style="resize: none;" rows="4" cols="50" name="description" id="description">{{ $api->description}}</textarea>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <label class="checkbox">
                            <input type="checkbox" id="active"
                            @if ( $api->active)
                            checked
                            @endif
                            name="active">&nbsp;Active
                        </label>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <button type="submit" class="btn">{{ Lang::get("messages.edit_api_form_save") }}</button>
                    </div>
                </div>
                <input type="hidden" name="id" id="id" value="{{ $api->id }}"/>
            </fieldset>
        </form>
    </div>
</div>

<!--scopes-->

<div class="row-fluid">
    <div class="span12">
        <div class="row-fluid">
            <h4 style="float:left"><i class="icon-info-sign accordion-toggle" title=""></i>&nbsp;Available Scopes</h4>
            <div style="position: relative;float:left;">
                <div style="position:absolute;top:13px;margin-left:5px"><i class="icon-refresh accordion-toggle refresh-scopes" title="Update Scopes List"></i></div>
            </div>
        </div>
        <div class="row-fluid">
            <div class="alert alert-info" id="info-scopes" style="display: none">
                <strong>There are not any available Scopes</strong>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12">
                {{ HTML::link(URL::action("ApiScopeController@create",null),'Register Scope',array('class'=>'btn add-scope','title'=>'Adds a New API Scope')) }}
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12">
                <table id='table-scopes' class="table table-hover table-condensed">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Active</th>
                        <th>Default</th>
                        <th>System</th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody id="body-scopes">
                    @foreach($api->scopes()->get() as $scope)
                    <tr>
                        <td>{{ $scope->name}}</td>
                        <td>
                            <input type="checkbox" data-scope-id="{{$scope->id}}" class="scope-active-checkbox" id="scope-active_{{$scope->id}}"
                            @if ( $scope->active)
                            checked
                            @endif
                            value="{{$scope->id}}"/>
                        </td>
                        <td>
                            <input type="checkbox" data-scope-id="{{$scope->id}}" class="scope-default-checkbox" id="scope-default_{{$scope->id}}"
                            @if ( $scope->default)
                            checked
                            @endif
                            value="{{$scope->id}}"/>
                        </td>
                        <td>
                            <input type="checkbox" data-scope-id="{{$scope->id}}" class="scope-system-checkbox" id="scope-system_{{$scope->id}}"
                            @if ( $scope->system)
                            checked
                            @endif
                            value="{{$scope->id}}"/>
                        </td>
                        <td>
                            &nbsp;
                            {{ HTML::link(URL::action("AdminController@editScope",array("id"=>$scope->id)),'Edit',array('class'=>'btn edit-scope','title'=>'Edits a Registered API Scope')) }}
                            {{ HTML::link(URL::action("ApiScopeController@delete",array("id"=>$scope->id)),'Delete',array('class'=>'btn delete-scope','title'=>'Deletes a Registered API Scope'))}}
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<div id="dialog-form-scope" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Register New API Scope</h3>
    </div>
    <div class="modal-body">
        <form id="form-scope" name="form-scope">
            <fieldset>
                <label for="name">Name</label>
                <input type="text" name="name" id="name">

                <label for="short_description">Short Description</label>
                <textarea style="resize: none;" rows="2" cols="50" name="short_description" id="short_description"></textarea>

                <label for="description">Description</label>
                <textarea style="resize: none;" rows="4" cols="50" name="description" id="description"></textarea>

                <label class="checkbox">
                    <input type="checkbox" id="default" name="default">&nbsp;Default
                </label>
                <label class="checkbox">
                    <input type="checkbox" id="system" name="system">&nbsp;System
                </label>

                <label class="checkbox">
                    <input type="checkbox" id="active" name="active">&nbsp;Active
                </label>
            </fieldset>
        </form>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <button id='save-scope' class="btn btn-primary">Save changes</button>
    </div>
</div>

<!-- endpoints-->

<div class="row-fluid">
    <div class="span12">
        <div class="row-fluid">
            <h4 style="float:left"><i class="icon-info-sign accordion-toggle" title=""></i>&nbsp;Available Endpoints</h4>
            <div style="position: relative;float:left;">
                <div style="position:absolute;top:13px;margin-left:5px"><i class="icon-refresh accordion-toggle refresh-endpoints" title="Update Endpoints List"></i></div>
            </div>
        </div>
        <div class="row-fluid">
            <div class="alert alert-info" id="info-endpoints" style="display: none">
                <strong>There are not any available Endpoints</strong>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12">
                {{ HTML::link(URL::action("ApiEndpointController@create",null),'Register Endpoint',array('class'=>'btn add-endpoint','title'=>'Adds a New API Endpoint')) }}
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12">
                <table id='table-endpoints' class="table table-hover table-condensed">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Active</th>
                        <th>Route</th>
                        <th>HTTP Method</th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody id="body-endpoints">
                    @foreach($api->endpoints()->get() as $endpoint)
                    <tr>
                        <td>{{ $endpoint->name }}</td>
                        <td>
                            <input type="checkbox" data-endpoint-id="{{$endpoint->id}}" class="endpoint-active-checkbox" id="endpoint-active_{{$endpoint->id}}"
                            @if ( $endpoint->active)
                            checked
                            @endif
                            value="{{$endpoint->id}}"/>
                        </td>
                        <td>{{$endpoint->route}}</td>
                        <td>{{$endpoint->http_method}}</td>
                        <td>
                            &nbsp;
                            {{ HTML::link(URL::action("AdminController@editEndpoint",array("id"=>$endpoint->id)),'Edit',array('class'=>'btn edit-endpoint','title'=>'Edits a Registered API Endpoint')) }}
                            {{ HTML::link(URL::action("ApiEndpointController@delete",array("id"=>$endpoint->id)),'Delete',array('class'=>'btn delete-endpoint','title'=>'Deletes a Registered API Endpoint'))}}
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="dialog-form-endpoint" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Register New API Endpoint</h3>
    </div>
    <div class="modal-body">
        <form id="form-endpoint" name="form-endpoint">
            <fieldset>
                <label for="name">Name</label>
                <input type="text" name="name" id="name">

                <label for="description">Description</label>
                <textarea style="resize: none;" rows="4" cols="50" name="description" id="description"></textarea>

                <label for="route">Route</label>
                <input type="text" name="route" id="route">

                <label for="rate_limit">Rate Limit (Per Hour)</label>
                <input type="text" name="rate_limit" id="rate_limit">

                <label for="http_method">HTTP Method</label>
                <select name="http_method" id="http_method">
                    <option value="GET">GET</option>
                    <option value="POST">POST</option>
                    <option value="PUT">PUT</option>
                    <option value="DELETE">DELETE</option>
                </select>

                <label class="checkbox">
                    <input type="checkbox" id="allow_cors" name="allow_cors">&nbsp;Allows CORS
                </label>

                <label class="checkbox">
                    <input type="checkbox" id="active" name="active">&nbsp;Active
                </label>

            </fieldset>
        </form>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <button id='save-endpoint' class="btn btn-primary">Save changes</button>
    </div>
</div>


@stop
@section('scripts')
<script type="application/javascript">

    var api_id = {{ $api->id}};

	var scopesUrls = {
		get : '{{ URL::action("ApiScopeController@getByPage",array("offset"=>1,"limit"=>1000,"api_id"=>$api->id)) }}',
		edit : '{{ URL::action("AdminController@editScope",array("id"=>"@id")) }}',
		delete : '{{ URL::action("ApiScopeController@delete",array("id"=>"@id")) }}',
		activate:'{{ URL::action("ApiScopeController@activate",array("id"=>"@id")) }}',
		deactivate: '{{ URL::action("ApiScopeController@deactivate",array("id"=>"@id")) }}',
		update : '{{URL::action("ApiScopeController@update")}}',
		add : '{{ URL::action("ApiScopeController@create") }}'
	};

	var endpointUrls = {
		get : '{{ URL::action("ApiEndpointController@getByPage",array("offset"=>1,"limit"=>1000,"api_id"=>$api->id)) }}',
		edit : '{{ URL::action("AdminController@editEndpoint",array("id"=>"@id")) }}',
		delete : '{{ URL::action("ApiEndpointController@delete",array("id"=>"@id")) }}',
		activate:'{{ URL::action("ApiEndpointController@activate",array("id"=>"@id")) }}',
		deactivate: '{{ URL::action("ApiEndpointController@deactivate",array("id"=>"@id")) }}',
		add : '{{ URL::action("ApiEndpointController@create") }}'
	};

    var editApiMessages = {
        success: '{{ Lang::get("messages.global_successfully_save_entity", array("entity" => "API")) }}'
    };
</script>
{{ HTML::script('assets/js/oauth2/profile/admin/edit-api.js') }}
@stop