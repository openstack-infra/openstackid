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

    function loadResourceServers(){
        var link = '{{URL::action("ApiResourceServerController@getByPage",array("offset"=>1,"limit"=>1000))}}';
        $.ajax(
            {
                type: "GET",
                url: link,
                dataType: "json",
                timeout:60000,
                success: function (data,textStatus,jqXHR) {
                    //load data...
                    var uris = data.page;
                    var template = $('<tbody><tr><td class="fname"></td><td class="hname"></td><td class="ip"></td><td class="active"><input type="checkbox" class="resource-server-active-checkbox"></td><td>&nbsp;<a class="btn edit-resource-server" title="Edits a Registered Resource Server">Edit</a>&nbsp;<a class="btn delete-resource-server" title="Deletes a Registered Resource Server">Delete</a></td></tr></tbody>');
                    var directives = {
                        'tr':{
                            'resource_server<-context':{
                                'td.fname':'resource_server.friendly_name',
                                'td.hname':'resource_server.host',
                                'td.ip':'resource_server.ip',
                                '.resource-server-active-checkbox@value':'resource_server.id',
                                '.resource-server-active-checkbox@checked':function(arg){
                                    return arg.item.active?'true':'';
                                },
                                '.resource-server-active-checkbox@data-resource-server-id':'resource_server.id',
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

      $('#server-admin','#main-menu').addClass('active');

      //validation rules on new server form
      var resource_server_form = $('#form-resource-server');
      var dialog_resource_server = $('#dialog-form-resource-server');

      var resource_server_validator = resource_server_form.validate({
            rules: {
                "host"  :        {required: true, nowhitespace:true,rangelength: [1, 512]},
                "friendly_name": {required: true, free_text:true,rangelength: [1, 255]},
                "ip":            {required: true, ipV4:true}
            }
      });

      dialog_resource_server.modal({
          show:false,
          backdrop:"static"
      });

      dialog_resource_server.on('hidden', function () {
          resource_server_form.cleanForm();
          resource_server_validator.resetForm();
      })

      $("body").on('click',".add-resource-server",function(event){
        dialog_resource_server.modal('show');
        event.preventDefault();
        return false;
      });

      $("body").on('click',".refresh-servers",function(event){
        loadResourceServers()
        event.preventDefault();
        return false;
      });


      $("body").on('click',".resource-server-active-checkbox",function(event){
          var active = $(this).is(':checked');
          var resource_server_id = $(this).attr('data-resource-server-id');
          var url    = active? '{{ URL::action("ApiResourceServerController@activate",array("id"=>"@id")) }}':'{{ URL::action("ApiResourceServerController@deactivate",array("id"=>"@id")) }}';
          url        = url.replace('@id',resource_server_id);
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

      $("body").on('click',"#save-resource-server",function(event){
        var is_valid = resource_server_form.valid();
        if (is_valid){
          var resource_server = resource_server_form.serializeForm();
          $.ajax({
              type: "POST",
              url: '{{URL::action("ApiResourceServerController@create",null)}}',
              data: JSON.stringify(resource_server),
              contentType: "application/json; charset=utf-8",
              dataType: "json",
              timeout:60000,
              success: function (data,textStatus,jqXHR) {
                  loadResourceServers();
                  dialog_resource_server.modal('hide');
              },
              error: function (jqXHR, textStatus, errorThrown) {
                  ajaxError(jqXHR, textStatus, errorThrown);
              }
          });
        }
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