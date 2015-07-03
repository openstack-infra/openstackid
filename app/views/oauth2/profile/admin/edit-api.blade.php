@extends('layout')

@section('title')
<title>Welcome to openstackId - Server Admin - Edit API</title>
@stop

@section('content')
@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
<a href='{{ URL::action("AdminController@editResourceServer",array("id"=>$api->resource_server_id)) }}'>{{ Lang::get("messages.edit_api_go_back") }}</a>
<legend>{{ Lang::get("messages.edit_api_title", array("id" => $api->id)) }}</legend>

<div class="row">
    <div class="col-md6">
        <form class="form-horizontal" id="api-form" name="api-form" action='{{URL::action("ApiController@update",null)}}'>
                <div class="form-group">
                    <label for="name">{{ Lang::get("messages.edit_api_form_name") }}</label>
                    <input class="form-control" type="text" name="name" id="name" value="{{ $api->name }}">
                </div>
                <div class="form-group">
                    <label for="description">{{ Lang::get("messages.edit_api_form_description") }}</label>
                    <textarea class="form-control" style="resize: none;" rows="4" cols="50" name="description" id="description">{{ $api->description}}</textarea>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="active"
                               @if ( $api->active)
                               checked
                               @endif
                               name="active">&nbsp;Active
                    </label>
                </div>
                <button type="submit" class="btn btn-default btn-md active">{{ Lang::get("messages.edit_api_form_save") }}</button>
                <input type="hidden" name="id" id="id" value="{{ $api->id }}"/>
        </form>
    </div>
</div>

<!--scopes-->

<div class="row">
    <div class="col-md-12">
        <div class="row">
            <h4 style="float:left"><span aria-hidden="true" class="glyphicon glyphicon-info-sign pointable" title="scopes available to assign to endpoints"></span>&nbsp;Available Scopes</h4>
            <div style="position: relative;float:left;">
                <div style="position:absolute;top:13px;margin-left:5px">
                    <span aria-hidden="true" class="glyphicon glyphicon-refresh pointable refresh-scopes"title="Update Scopes List"></span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="alert alert-info" id="info-scopes" style="display: none">
                <strong>There are not any available Scopes</strong>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                {{ HTML::link(URL::action("ApiScopeController@create",null),'Register Scope',array('class'=>'btn active btn-primary add-scope','title'=>'Adds a New API Scope')) }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
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
                            {{ HTML::link(URL::action("AdminController@editScope",array("id"=>$scope->id)),'Edit',array('class'=>'btn btn-default active edit-scope','title'=>'Edits a Registered API Scope')) }}
                            {{ HTML::link(URL::action("ApiScopeController@delete",array("id"=>$scope->id)),'Delete',array('class'=>'btn btn-default btn-delete active delete-scope','title'=>'Deletes a Registered API Scope'))}}
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('modal', array ('modal_id' => 'dialog-form-scope', 'modal_title' => 'Register New API Scope', 'modal_save_css_class' => 'save-scope', 'modal_save_text' => 'Save', 'modal_form' => 'oauth2.profile.admin.scope-add-form', 'modal_form_data' => array()))

<!-- endpoints-->

<div class="row">
    <div class="col-md-12">
        <div class="row">
            <h4 style="float:left"><span aria-hidden="true" class="glyphicon glyphicon-info-sign pointable" title=""></span>&nbsp;Available Endpoints</h4>
            <div style="position: relative;float:left;">
                <div style="position:absolute;top:13px;margin-left:5px">
                    <span aria-hidden="true" class="glyphicon glyphicon-refresh pointable refresh-endpoints"title="Update Endpoints List"></span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="alert alert-info" id="info-endpoints" style="display: none">
                <strong>There are not any available Endpoints</strong>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                {{ HTML::link(URL::action("ApiEndpointController@create",null),'Register Endpoint',array('class'=>'btn active btn-primary add-endpoint','title'=>'Adds a New API Endpoint')) }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
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
                            {{ HTML::link(URL::action("AdminController@editEndpoint",array("id"=>$endpoint->id)),'Edit',array('class'=>'btn btn-default active edit-endpoint','title'=>'Edits a Registered API Endpoint')) }}
                            {{ HTML::link(URL::action("ApiEndpointController@delete",array("id"=>$endpoint->id)),'Delete',array('class'=>'btn btn-default btn-delete active delete-endpoint','title'=>'Deletes a Registered API Endpoint'))}}
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('modal', array ('modal_id' => 'dialog-form-endpoint', 'modal_title' => 'Register New API Endpoint', 'modal_save_css_class' => 'save-endpoint', 'modal_save_text' => 'Save', 'modal_form' => 'oauth2.profile.admin.endpoint-add-form', 'modal_form_data' => array()))
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

@append