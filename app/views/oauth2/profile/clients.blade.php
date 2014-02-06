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

    function loadClients(){
        $.ajax(
            {
                type: "GET",
                url: '{{ URL::action("ClientApiController@getByPage",array("offset"=>1,"limit"=>1000,"user_id"=>$user_id ))}}',
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                timeout:60000,
                success: function (data,textStatus,jqXHR) {
                    //load data...
                    var clients = data.page;
                    var template = $('<tbody><tr><td class="app-name"></td><td class="client-type"></td><td class="client-active"><input type="checkbox" class="app-active-checkbox"></td><td class="client-locked"><input type="checkbox" disabled="disabled" class="app-locked-checkbox"></td><td class="client-modified"></td><td class="client-actions">&nbsp;<a class="btn edit-client" title="Edits a Registered Application">Edit</a>&nbsp;<a class="btn del-client" title="Deletes a Registered Application">Delete</a></td></tr></tbody>');
                    var directives = {
                        'tr':{
                            'client<-context':{
                                'td.app-name':'client.app_name',
                                'td.client-type':'client.application_type',
                                'td.client-modified':'client.updated_at',
                                '.app-active-checkbox@value':'client.id',
                                '.app-active-checkbox@checked':function(arg){
                                    var client_active = parseInt(arg.item.active);
                                    return client_active===1?'true':'';
                                },
                                '.app-active-checkbox@id':function(arg){
                                    var client_id = arg.item.id;
                                    return 'app-active_'+client_id;
                                },
                                '.app-locked-checkbox@value':'client.id',
                                '.app-locked-checkbox@id':function(arg){
                                    var client_id = arg.item.id;
                                    return 'app-locked_'+client_id;
                                },
                                '.app-locked-checkbox@checked':function(arg){
                                    var client_locked = parseInt(arg.item.locked);
                                    return client_locked===1?'true':'';
                                },
                                'a.edit-client@href':function(arg){
                                    var client_id = arg.item.id;
                                    var href = '{{ URL::action("AdminController@editRegisteredClient",array("id"=>"@id")) }}';
                                    return href.replace('@id',client_id);
                                },
                                'a.del-client@href':function(arg){
                                    var client_id = arg.item.id;
                                    var href = '{{ URL::action("ClientApiController@delete",array("id"=>"@id")) }}';
                                    return href.replace('@id',client_id);
                                }
                            }
                        }
                    };
                    var body = template.render(clients, directives);
                    var table = $('<table id="tclients" class="table table-hover table-condensed"><thead><tr><th>Application Name</th><th>Type</th><th>Is Active</th><th>Is Locked</th><th>Modified</th><th>&nbsp;</th></tr></thead>'+body.html()+'</table>');
                    $('#tclients','#clients').remove();
                    $('#clients').append(table);

                },
                error: function (jqXHR, textStatus, errorThrown) {
                    ajaxError(jqXHR, textStatus, errorThrown);
                }
            }
        );
    }

    $(document).ready(function() {

        $('#oauth2-console','#main-menu').addClass('active');

        var application_form = $('#form-application');
        var application_dialog = $("#dialog-form-application");
        var application_validator = application_form.validate({
            rules: {
                "app_name" : {required: true, nowhitespace:true,rangelength: [1, 255]},
                "app_description" : {required: true, free_text:true,rangelength: [1, 512]},
                "website" : {required:true,url:true}
            }
        });

        application_dialog.modal({
            show:false,
            backdrop:"static"
        });

        application_dialog.on('hidden', function () {
            application_form.cleanForm();
            application_validator.resetForm();
        })

        $("body").on('click',".add-client",function(event){
            application_dialog.modal('show');
            event.preventDefault();
            return false;
        });

        $("body").on('click',"#save-application",function(event){
            var is_valid        = application_form.valid();
            if (is_valid){
                var application     = application_form.serializeForm();
                application.user_id = {{$user_id}};
                var link = $(this).attr('href');
                $.ajax({
                    type: "POST",
                    url: '{{URL::action("ClientApiController@create",null)}}',
                    data: JSON.stringify(application),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        loadClients();
                        application_dialog.modal('hide');
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        ajaxError(jqXHR, textStatus, errorThrown);
                    }
                });
            }
            event.preventDefault();
            return false;
        });

        $("body").on('click',".del-client",function(event){
            if(confirm("Are you sure to delete this registered application?")){
                var url = $(this).attr('href');
                $.ajax(
                    {
                        type: "DELETE",
                        url: url,
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            loadClients();
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

        $("body").on('click',".app-active-checkbox",function(event){
            var client_id = $(this).attr('value');
            var url       = '{{ URL::action("ClientApiController@updateStatus",array("id"=>"@id","active"=>"@active")) }}'
            url           = url.replace('@id',client_id);
            url           = url.replace('@active',$(this).is(':checked'));
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
    });
</script>
@stop

