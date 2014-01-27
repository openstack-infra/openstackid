@extends('layout')

@section('title')
<title>Welcome to openstackId - Edit Resource Server</title>
@stop

@section('content')
<a href="{{ URL::previous() }}">Go Back</a>

<legend>Edit Resource Server - Id {{ $resource_server->id }}</legend>

<div class="row-fluid">
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
                    <button type="submit" class="btn">Save</button>
                </div>
            </div>
            <input type="hidden" name="id" id="id" value="{{ $resource_server->id }}"/>
        </fieldset>
    </form>
</div>

<div class="row-fluid">
    <legend><i class="icon-info-sign accordion-toggle" title=""></i>&nbsp;Available Apis</legend>
    {{ HTML::link(URL::action("ApiResourceServerController@create",null),'Register Resource Server API',array('class'=>'btn add-resource-server-api','title'=>'Adds a New Resource Server API')) }}
    <table id='table-resource-serves-apis' class="table table-hover table-condensed">
        <thead>
        <tr>
            <th>&nbsp;</th>
            <th>Name</th>
            <th>Active</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody id="body-resource-server-apis">
        @foreach($resource_server->apis()->get() as $api)
        <tr>
            <td><img src="{{ $api->getLogo()}}"  height="24" width="24" alt="{{ $api->name}} logo"/></td>
            <td>{{ $api->name}}</td>
            <td>
                <input type="checkbox" class="resource-server-api-active-checkbox" id="resource-server-api-active_{{$api->id}}"
                @if ( $api->active)
                checked
                @endif
                value="{{$api->id}}"/>
            </td>
            <td>
                &nbsp;
                {{ HTML::link(URL::action("AdminController@editApi",array("id"=>$api->id)),'Edit',array('class'=>'btn edit-resource-server-api','title'=>'Edits a Registered Resource Server API')) }}
                {{ HTML::link(URL::action("ApiController@delete",array("id"=>$api->id)),'Delete',array('class'=>'btn delete-resource-server-api','title'=>'Deletes a Registered Resource Server API'))}}
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>


<div id="dialog-form-register-new-resource-server-api" title="Register New Resource Server API">
    <form id="form-register-new-resource-server-api" name="form-register-new-resource-server-api">
        <fieldset>
            <label for="name">Name</label>
            <input type="text" name="name" id="name">
            <label for="description">Description</label>
            <textarea style="resize: none;" rows="4" cols="50" name="description" id="description"></textarea>
        </fieldset>
    </form>
</div>

@stop

@section('scripts')
<script type="application/javascript">

    function loadApis(){

    }


    $(document).ready(function() {

        var resource_server_form = $('#resource-server-form');

        resource_server_form.submit(function( event ) {
            var resource_server = $(this).serializeForm();
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
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        ajaxError(jqXHR, textStatus, errorThrown);
                    }
                }
            );
            event.preventDefault();
            return false;
        });

        $("#dialog-form-register-new-resource-server-api").dialog({
            autoOpen: false,
            height: 430,
            width: 435,
            modal: true,
            resizable: false,
            close: function( event, ui ) {

            },
            buttons: {
                "Register": function() {

                },
                Cancel: function() {
                    $(this).dialog( "close" );
                }
            }
        });

        $("body").on('click',".add-resource-server-api",function(event){
            $("#dialog-form-register-new-resource-server-api").dialog( "open" );
            event.preventDefault();
            return false;
        });

        $("body").on('click',".delete-resource-server-api",function(event){
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