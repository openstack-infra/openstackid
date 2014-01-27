@extends('layout')

@section('title')
<title>Welcome to openstackId - Edit Resource Servers</title>
@stop

@section('content')
<a href="{{ URL::previous() }}">Go Back</a>
<div class="row-fluid">

    <legend><i class="icon-info-sign accordion-toggle" title=""></i>&nbsp;Resource Servers</legend>
    {{ HTML::link(URL::action("ApiResourceServerController@create",null),'Register Resource Server',array('class'=>'btn add-resource-server','title'=>'Adds a New Resource Server')) }}
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

<div id="dialog-form-register-new-resource-server" title="Register New Resource Server">
    <form id="form-register-new-resource-server" name="form-register-new-resource-server">
        <fieldset>
            <label for="name">Host</label>
            <input type="text" name="host" id="host">
            <label for="friendly_name">Friendly Name</label>
            <input type="text" name="friendly_name" id="friendly_name">
            <label for="ip">IP Address</label>
            <input type="text" name="ip" id="ip">
        </fieldset>
    </form>
</div>
@stop

@section('scripts')
<script type="application/javascript">

    function loadResourceServers(){
        var link = '{{URL::action("ApiResourceServerController@getByPage",array("page_nbr"=>1,"page_size"=>1000))}}';
        $.ajax(
            {
                type: "GET",
                url: link,
                dataType: "json",
                timeout:60000,
                success: function (data,textStatus,jqXHR) {
                    //load data...
                    var uris = data.page;
                    var template = $('<tbody><tr><td class="fname"></td><td class="hname"></td><td class="ip"></td><td class="active"><input type="checkbox" class="resource-server-active-checkbox"></td><td>&nbsp;<a class="btn edit-resource-server" title="Edits a Registered Resource Server">Edit</a><a class="btn delete-resource-server" title="Deletes a Registered Resource Server">Delete</a></td></tr></tbody>');
                    var directives = {
                        'tr':{
                            'resource_server<-context':{
                                'td.fname':'resource_server.friendly_name',
                                'td.hname':'resource_server.host',
                                'td.ip':'resource_server.ip',
                                '.resource-server-active-checkbox@value':'resource_server.id',
                                '.resource-server-active-checkbox@checked':function(arg){
                                    var active = parseInt(arg.item.active);
                                    return active===1?'true':'';
                                },
                                '.resource-server-active-checkbox@id':function(arg){
                                    var id = arg.item.id;
                                    return 'resource-server-active_'+id;
                                },
                                'a.edit-resource-server@href':function(arg){
                                    var id = arg.item.id;
                                    var href = '{{ URL::action("AdminController@editResourceServer",array("id"=>-1)) }}';
                                    return href.replace('-1',id);
                                },
                                'a.delete-resource-server@href':function(arg){
                                    var id = arg.item.id;
                                    var href = '{{ URL::action("ApiResourceServerController@delete",array("id"=>-1)) }}';
                                    return href.replace('-1',id);
                                }
                            }
                        }
                    };
                    var html = template.render(uris, directives);
                    $('#body-resource-servers').html(html.html());
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    ajaxError(jqXHR, textStatus, errorThrown);
                }
            }
        );
    }

    $(document).ready(function() {

      //validation rules on new server form
      var resource_server_form = $('#form-register-new-resource-server');

      var resource_server_validator = resource_server_form.validate({
            rules: {
                "host"  :        {required: true, nowhitespace:true,rangelength: [1, 512]},
                "friendly_name": {required: true, letterswithbasicpunc:true,rangelength: [1, 255]},
                "ip":            {required: true, ipV4:true}
            },
            onfocusout: false,
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    validator.errorList[0].element.focus();
                }
            },
            highlight: function(element) {
                $(element).parent().addClass("error");
            },
            unhighlight: function(element) {
                $(element).parent().removeClass("error");
            }
      });


      $("#dialog-form-register-new-resource-server").dialog({
          autoOpen: false,
          height: 430,
          width: 435,
          modal: true,
          resizable: false,
          close: function( event, ui ) {
              resource_server_form.cleanForm();
              resource_server_validator.resetForm();
          },
          buttons: {
              "Register": function() {
                  var is_valid = resource_server_form.valid();
                  if (!is_valid) return;
                  var resource_server = resource_server_form.serializeForm();
                  resource_server.active = true;
                  $(this).dialog( "close" );

                  $.ajax({
                    type: "POST",
                    url: '{{URL::action("ApiResourceServerController@create",null)}}',
                    data: JSON.stringify(resource_server),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        loadResourceServers();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        ajaxError(jqXHR, textStatus, errorThrown);
                    }
                  });
              },
              Cancel: function() {
                  $(this).dialog( "close" );
              }
          }
      });

      $("body").on('click',".add-resource-server",function(event){
        $("#dialog-form-register-new-resource-server").dialog( "open" );
        event.preventDefault();
        return false;
      });

      $("body").on('click',".delete-resource-server",function(event){
            if(confirm("Are you sure? this would delete all related registered apis, endpoints and associated scopes.")){
                var href = $(this).attr('href');
                $.ajax(
                    {
                        type: "DELETE",
                        url: href,
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            loadResourceServers();
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