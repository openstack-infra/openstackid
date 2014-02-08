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

    function loadScopes(){
        $.ajax({
            type: "GET",
            url: '{{ URL::action("ApiScopeController@getByPage",array("offset"=>1,"limit"=>1000,"api_id"=>$api->id)) }}',
            contentType: "application/json; charset=utf-8",
            timeout:60000,
            success: function (data,textStatus,jqXHR) {
                var scopes = data.page;
                if(scopes.length>0){
                    $('#info-scopes').hide();
                    $('#table-scopes').show();
                    var template = $('<tbody><tr>' +
                        '<td class="name"></td>' +
                        '<td class="active"><input type="checkbox" class="scope-active-checkbox"></td>' +
                        '<td class="default"><input type="checkbox" class="scope-default-checkbox"></td>' +
                        '<td class="system"><input type="checkbox" class="scope-system-checkbox"></td>' +
                        '<td>&nbsp;' +
                        '<a class="btn edit-scope" title="Edits a Registered API Scope">Edit</a>&nbsp;' +
                        '<a class="btn delete-scope" title="Deletes a Registered API Scope">Delete</a>' +
                        '</td></tr></tbody>');
                    var directives = {
                        'tr':{
                            'scope<-context':{
                                'td.name':'scope.name',
                                //active
                                '.scope-active-checkbox@value':'scope.id',
                                '.scope-active-checkbox@checked':function(arg){
                                    return arg.item.active?'true':'';
                                },
                                '.scope-active-checkbox@id':function(arg){
                                    var id = arg.item.id;
                                    return 'scope-active_'+id;
                                },
                                '.scope-active-checkbox@data-scope-id':'scope.id',
                                //default
                                '.scope-default-checkbox@value':'scope.id',
                                '.scope-default-checkbox@checked':function(arg){
                                    return arg.item.default?'true':'';
                                },
                                '.scope-default-checkbox@id':function(arg){
                                    var id = arg.item.id;
                                    return 'scope-default_'+id;
                                },
                                '.scope-default-checkbox@data-scope-id':'scope.id',
                                //system
                                '.scope-system-checkbox@value':'scope.id',
                                '.scope-system-checkbox@checked':function(arg){
                                    return arg.item.system?'true':'';
                                },
                                '.scope-system-checkbox@id':function(arg){
                                    var id = arg.item.id;
                                    return 'scope-system_'+id;
                                },
                                '.scope-system-checkbox@data-scope-id':'scope.id',
                                //buttons
                                'a.edit-scope@href':function(arg){
                                    var id = arg.item.id;
                                    var href = '{{ URL::action("AdminController@editScope",array("id"=>"@id")) }}';
                                    return href.replace('@id',id);
                                },
                                'a.delete-scope@href':function(arg){
                                    var id = arg.item.id;
                                    var href = '{{ URL::action("ApiScopeController@delete",array("id"=>"@id")) }}';
                                    return href.replace('@id',id);
                                }
                            }
                        }
                    };
                    var html = template.render(scopes, directives);
                    $('#body-scopes').html(html.html());
                }
                else{
                    $('#info-scopes').show();
                    $('#table-scopes').hide();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                ajaxError(jqXHR, textStatus, errorThrown);
            }
        });
    }

    function loadEndpoints(){
        $.ajax({
            type: "GET",
            url: '{{ URL::action("ApiEndpointController@getByPage",array("offset"=>1,"limit"=>1000,"api_id"=>$api->id)) }}',
            contentType: "application/json; charset=utf-8",
            timeout:60000,
            success: function (data,textStatus,jqXHR) {
                var endpoints = data.page;
                if(endpoints.length>0){
                    $('#info-endpoints').hide();
                    $('#table-endpoints').show();
                    var template = $('<tbody><tr>' +
                        '<td class="name"></td>' +
                        '<td class="active"><input type="checkbox" class="endpoint-active-checkbox"></td>' +
                        '<td class="route"></td>' +
                        '<td class="method"></td>' +
                        '<td>&nbsp;' +
                        '<a class="btn edit-endpoint" title="Edits a Registered API Endpoint">Edit</a>&nbsp;' +
                        '<a class="btn delete-endpoint" title="Deletes a Registered API Endpoint">Delete</a>' +
                        '</td></tr></tbody>');
                        var directives = {
                        'tr':{
                            'endpoint<-context':{
                                'td.name':'endpoint.name',
                                'td.route':'endpoint.route',
                                'td.method':'endpoint.http_method',
                                //active
                                '.endpoint-active-checkbox@value':'scope.id',
                                '.endpoint-active-checkbox@checked':function(arg){
                                    return arg.item.active?'true':'';
                                },
                                '.endpoint-active-checkbox@id':function(arg){
                                    var id = arg.item.id;
                                    return 'endpoint-active_'+id;
                                },
                                '.endpoint-active-checkbox@data-endpoint-id':'endpoint.id',
                                //buttons
                                'a.edit-endpoint@href':function(arg){
                                    var id = arg.item.id;
                                    var href = '{{ URL::action("AdminController@editEndpoint",array("id"=>"@id")) }}';
                                    return href.replace('@id',id);
                                },
                                'a.delete-endpoint@href':function(arg){
                                    var id = arg.item.id;
                                    var href = '{{ URL::action("ApiEndpointController@delete",array("id"=>"@id")) }}';
                                    return href.replace('@id',id);
                                }
                            }
                        }
                    };
                    var html = template.render(endpoints, directives);
                    $('#body-endpoints').html(html.html());
                }
                else{
                    $('#info-endpoints').show();
                    $('#table-endpoints').hide();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                ajaxError(jqXHR, textStatus, errorThrown);
            }
        });
    }

    $(document).ready(function() {

        $('#server-admin','#main-menu').addClass('active');

        var api_form = $('#api-form');
        var api_validator = api_form.validate({
            rules: {
                "name"  :        {required: true, nowhitespace:true,rangelength: [1, 255]},
                "description":   {required: true, free_text:true,rangelength: [1, 512]}
            }
        });

        api_form.submit(function( event ) {
            var is_valid = api_form.valid();
            if (is_valid){
                api_validator.resetForm();
                var api = api_form.serializeForm();
                var href = $(this).attr('action');
                $.ajax(
                    {
                        type: "PUT",
                        url: href,
                        data: JSON.stringify(api),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            displaySuccessMessage('{{ Lang::get("messages.global_successfully_save_entity", array("entity" => "API")) }}',api_form);
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

        //scopes

        if($('#table-scopes tr').length===1){
            $('#info-scopes').show();
            $('#table-scopes').hide();
        }


        $("body").on('click','.scope-active-checkbox',function(event){
            var id     = $(this).attr('data-scope-id');
            var active = $(this).is(':checked');

            var url       = active?'{{ URL::action("ApiScopeController@activate",array("id"=>"@id")) }}':'{{ URL::action("ApiScopeController@deactivate",array("id"=>"@id")) }}';
            url           = url.replace('@id',id);
			var verb      = active?'PUT':'DELETE'

            $.ajax(
                {
                    type: verb,
                    url: url,
                    contentType: "application/json; charset=utf-8",
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        ajaxError(jqXHR, textStatus, errorThrown);
                    }
                }
            );
        });

        $("body").on('click','.scope-default-checkbox',function(event){
            var id         = $(this).attr('data-scope-id');
            var is_default = $(this).is(':checked');

            var scope = { id : id, default:is_default};

            $.ajax(
                {
                    type: "PUT",
                    url: '{{URL::action("ApiScopeController@update") }}',
                    data: JSON.stringify(scope),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        ajaxError(jqXHR, textStatus, errorThrown);
                    }
                }
            );
        });

        $("body").on('click','.scope-system-checkbox',function(event){
            var id         = $(this).attr('data-scope-id');
            var is_system  = $(this).is(':checked');

            var scope = { id : id, system:is_system};

            $.ajax(
                {
                    type: "PUT",
                    url: '{{URL::action("ApiScopeController@update") }}',
                    data: JSON.stringify(scope),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        ajaxError(jqXHR, textStatus, errorThrown);
                    }
                }
            );

        });

        $("body").on('click','.refresh-scopes',function(event){
            loadScopes();
            event.preventDefault();
            return false;
        });

        var scope_dialog = $('#dialog-form-scope');
        var scope_form   = $('#form-scope');

        var scope_validator = scope_form.validate({
            rules: {
                "name"  :              {required: true, scopename:true,rangelength: [1, 512]},
                "short_description":   {required: true, free_text:true,rangelength: [1, 512]},
                "description":         {required: true, free_text:true,rangelength: [1, 1024]}
            }
        });

        scope_dialog.modal({
            show:false,
            backdrop:"static"
        });

        scope_dialog.on('hidden', function () {
            scope_form.cleanForm();
            scope_validator.resetForm();
        })

        $("body").on('click',".add-scope",function(event){
            scope_dialog.modal('show');
            event.preventDefault();
            return false;
        });

        $("body").on('click',"#save-scope",function(event){
            var is_valid = scope_form.valid();
            if (is_valid){
                var scope    = scope_form.serializeForm();
                scope.api_id = api_id;
                $.ajax(
                    {
                        type: "POST",
                        url: '{{ URL::action("ApiScopeController@create") }}',
                        data: JSON.stringify(scope),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            loadScopes();
                            scope_dialog.modal('hide');
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

        $("body").on('click',".delete-scope",function(event){
            if(confirm("Are you sure? this will delete all application assigned scopes and all endpoints assigned scopes too.")){
                var url = $(this).attr('href');
                $.ajax(
                    {
                        type: "DELETE",
                        url: url,
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            loadScopes();
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

        //endpoints

        var endpoint_dialog = $('#dialog-form-endpoint');
        var endpoint_form   = $('#form-endpoint');

        if($('#table-endpoints tr').length===1){
            $('#info-endpoints').show();
            $('#table-endpoints').hide();
        }

        $("body").on('click','.endpoint-active-checkbox',function(event){
            var id     = $(this).attr('data-endpoint-id');
            var active = $(this).is(':checked');
            var url    = active?'{{ URL::action("ApiEndpointController@activate",array("id"=>"@id")) }}':'{{ URL::action("ApiEndpointController@deactivate",array("id"=>"@id")) }}';
            url        = url.replace('@id',id);
	        var verb   = active?'PUT':'DELETE';
            $.ajax(
                {
                    type: verb,
                    url: url,
                    contentType: "application/json; charset=utf-8",
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        ajaxError(jqXHR, textStatus, errorThrown);
                    }
                }
            );
        });

        $("body").on('click','.refresh-endpoints',function(event){
            loadEndpoints();
            event.preventDefault();
            return false;
        });

        var endpoint_validator = endpoint_form.validate({
                rules: {
                    "name"  :     {required: true, nowhitespace:true,rangelength: [1, 255]},
                    "description":{required: true, free_text:true,rangelength: [1, 1024]},
                    "route":      {required: true,endpointroute:true,rangelength: [1, 1024]}
                }
         });

        endpoint_dialog.modal({
            show:false,
            backdrop:"static"
        });

        endpoint_dialog.on('hidden', function () {
            endpoint_form.cleanForm();
            endpoint_validator.resetForm();
        })

        $("body").on('click',".add-endpoint",function(event){
            endpoint_dialog.modal('show');
            event.preventDefault();
            return false;
        });

        $("body").on('click',"#save-endpoint",function(event){
            var is_valid = endpoint_form.valid();
            if (is_valid){
                var endpoint    = endpoint_form.serializeForm();
                endpoint.api_id = api_id;
                $.ajax(
                    {
                        type: "POST",
                        url: '{{ URL::action("ApiEndpointController@create") }}',
                        data: JSON.stringify(endpoint),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            loadEndpoints();
                            endpoint_dialog.modal('hide');
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

        $("body").on('click',".delete-endpoint",function(event){
            if(confirm("Are you sure? this will delete the selected endpoint.")){
                var url = $(this).attr('href');
                $.ajax(
                    {
                        type: "DELETE",
                        url: url,
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            loadEndpoints();
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
