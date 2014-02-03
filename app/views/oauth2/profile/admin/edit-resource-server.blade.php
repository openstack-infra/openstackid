@extends('layout')

@section('title')
<title>Welcome to openstackId - Edit Resource Server</title>
@stop

@section('content')
<a href="{{ URL::action("AdminController@listResourceServers",null) }}">Go Back</a>

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

    function loadApis(){
        $.ajax({
            type: "GET",
            url: '{{ URL::action("ApiController@getByPage",array("offset"=>1,"limit"=>1000))."?filters=".urlencode("resource_server_id:=:").$resource_server->id }}',
            contentType: "application/json; charset=utf-8",
            timeout:60000,
            success: function (data,textStatus,jqXHR) {
                var apis = data.page;
                if(apis.length>0){
                    $('#info-apis').hide();
                    $('#table-apis').show();
                    var template = $('<tbody><tr><td class="image"><img height="24" width="24"/></td><td class="name"></td><td class="active"><input type="checkbox" class="api-active-checkbox"></td><td>&nbsp;<a class="btn edit-api" title="Edits a Registered Resource Server API">Edit</a>&nbsp;<a class="btn delete-api" title="Deletes a Registered Resource Server API">Delete</a></td></tr></tbody>');
                    var directives = {
                    'tr':{
                        'api<-context':{
                            'img@src':function(arg){
                                var logo = arg.item.logo;
                                if(logo == null || logo=='') logo = "{{asset('img/apis/server.png');}}";
                                return logo;
                            },
                            'img@alt':'api.name',
                            'td.name':'api.name',
                            '.api-active-checkbox@value':'api.id',
                            '.api-active-checkbox@checked':function(arg){
                                var active = parseInt(arg.item.active);
                                return active===1?'true':'';
                            },
                            '.api-active-checkbox@id':function(arg){
                                var id = arg.item.id;
                                return 'resource-server-api-active_'+id;
                            },
                            '.api-active-checkbox@data-api-id':'api.id',
                            'a.edit-api@href':function(arg){
                                var id = arg.item.id;
                                var href = '{{ URL::action("AdminController@editApi",array("id"=>-1)) }}';
                                return href.replace('-1',id);
                            },
                            'a.delete-api@href':function(arg){
                                var id = arg.item.id;
                                var href = '{{ URL::action("ApiController@delete",array("id"=>-1)) }}';
                                return href.replace('-1',id);
                            }
                        }
                    }
                    };
                    var html = template.render(apis, directives);
                    $('#body-apis').html(html.html());
                }
                else{
                    $('#info-apis').show();
                    $('#table-apis').hide();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                ajaxError(jqXHR, textStatus, errorThrown);
            }
        });
    }

    $(document).ready(function() {

        if($('#table-apis tr').length===1){
            $('#info-apis').show();
            $('#table-apis').hide();
        }

        $("body").on('click','.refresh-apis',function(event){
            loadApis();
            event.preventDefault();
            return false;
        });

        var resource_server_form = $('#resource-server-form');

        var api_form = $('#form-api');
        var api_dialog = $('#dialog-form-api');

        api_dialog.modal({
            show:false,
            backdrop:"static"
        });

        var resource_server_validator = resource_server_form.validate({
            rules: {
                "host"  :        {required: true, nowhitespace:true,rangelength: [1, 512]},
                "friendly_name": {required: true, free_text:true,rangelength: [1, 255]},
                "ip":            {required: true, ipV4:true}
            }
        });

        var api_validator = api_form.validate({
            rules: {
                "name"  :        {required: true, nowhitespace:true,rangelength: [1, 255]},
                "description":   {required: true, free_text:true,rangelength: [1, 512]}
            }
        });


        api_dialog.on('hidden', function () {
            api_form.cleanForm();
            api_validator.resetForm();
        })

        $("body").on('click','#save-api',function(event){
            var is_valid = api_form.valid();
            if (is_valid){
                var api = api_form.serializeForm();
                api.resource_server_id = resource_server_id;
                $.ajax({
                    type: "POST",
                    url: '{{URL::action("ApiController@create",null)}}',
                    data: JSON.stringify(api),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        loadApis();
                        api_dialog.modal('hide');
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        ajaxError(jqXHR, textStatus, errorThrown);
                    }
                });
            }
            event.preventDefault();
            return false;
        });

        resource_server_form.submit(function( event ) {

            var is_valid = resource_server_form.valid();

            if (is_valid){
                resource_server_validator.resetForm();
                var resource_server = resource_server_form.serializeForm();
                var href = $(this).attr('action');
                $.ajax(
                    {
                        type: "PUT",
                        url: href,
                        data: JSON.stringify(resource_server),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            displaySuccessMessage('{{ Lang::get("messages.global_successfull_save_entity", array("entity" => "Resource Server")) }}',resource_server_form);
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

        $("body").on('click',".add-api",function(event){
            api_dialog.modal('show');
            event.preventDefault();
            return false;
        });

        $("body").on('click',".api-active-checkbox",function(event){
            var active = $(this).is(':checked');
            var api_id = $(this).attr('data-api-id');
            var url    = '{{ URL::action("ApiController@updateStatus",array("id"=>"@id","active"=>"@active")) }}';
            url        = url.replace('@id',api_id);
            url        = url.replace('@active',active);
            $.ajax(
                {
                    type: "PUT",
                    url: url,
                    contentType: "application/json; charset=utf-8",
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

        $("body").on('click',".delete-api",function(event){
            if(confirm("Are you sure? this would delete all related registered endpoints and associated scopes.")){
                var href = $(this).attr('href');
                $.ajax(
                    {
                        type: "DELETE",
                        url: href,
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            loadApis();
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