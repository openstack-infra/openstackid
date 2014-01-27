@extends('layout')

@section('title')
<title>Welcome to openstackId - Edit Profile</title>
@stop

@section('content')
<div class="span8" id="sidebar">

    <div class="row-fluid">
        <div class="span12">
            Hello, {{{ $username }}}.
            <a class="btn btn-small" href="{{ URL::action("UserController@logout") }}">logout</a>
            <div>Your OPENID: <a href="{{ str_replace("%23","#",$openid_url) }}">{{ str_replace("%23","#",$openid_url) }}</a></div>
        </div>
    </div>

    <div class="row-fluid">
        <div class="span12">
            {{ Form::open(array('url' => URL::action('UserController@postUserProfileOptions'), 'method' => 'post')) }}
            <legend><i class="icon-info-sign accordion-toggle" title="this information will be public on your profile page"></i>&nbsp;Edit your profile options:</legend>
            <fieldset>
                <label class="checkbox">
                    {{ Form::checkbox('show_full_name', '1', $show_full_name) }}Show Full Name
                </label>
                <label class="checkbox">
                    {{ Form::checkbox('show_email', '1', $show_email) }}Show Email
                </label>
                <label class="checkbox">
                    {{ Form::checkbox('show_pic', '1', $show_pic) }}Show Photo
                </label>
                <div class="pull-right">
                    {{ Form::submit('Save',array('id'=>'save','class'=>'btn')) }}
                </div>
            </fieldset>
            {{ Form::close() }}
        </div>
    </div>

    @if (count($sites)>0)
    <div class="row-fluid">
        <div id="trusted_sites" class="span12">
            <legend><i class="icon-info-sign accordion-toggle" title="Users can keep track of their trusted sites and manage them"></i>&nbsp;Trusted Sites</legend>
            <table class="table table-hover table-condensed">
                <thead>
                    <tr>
                        <th>Realm</th>
                        <th>Policy</th>
                        <th>Trusted Data</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($sites as $site)
                    @if($site->getAuthorizationPolicy()=='AllowForever')
                        <tr class="success">
                    @else
                        <tr class="error">
                    @endif
                        <td>{{ $site->getRealm() }}</td>
                        <td>{{ $site->getAuthorizationPolicy()}}</td>
                        <td>{{ $site->getUITrustedData() }}</td>
                        <td>{{ HTML::link(URL::action("UserController@deleteTrustedSite",array("id"=>$site->id)),'Delete',array('class'=>'btn del-realm','title'=>'Deletes a decision about a particular trusted site,')) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($is_server_admin)
    {{ HTML::link(URL::action("AdminController@listResourceServers",null),'Edit Resource Servers',array('class'=>'btn edit-resource-server','title'=>'Edits Resource Servers')) }}
    @endif

    <div class="row-fluid">
        <div id="clients" class="span12">
            <legend><i class="icon-info-sign accordion-toggle" title="Users can keep track of their registered applications and manage them"></i>&nbsp;Registered Applications</legend>
            {{ HTML::link(URL::action("ClientApiController@create",null),'Register Application',array('class'=>'btn add-client','title'=>'Adds a Registered Application')) }}
            @if (count($clients)>0)
            <table id='tclients' class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th>Application Name</th>
                    <th>Type</th>
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
                        <td>{{ $client->getFriendlyClientType()}}</td>
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
                            {{ HTML::link(URL::action("UserController@getEditRegisteredClient",array("id"=>$client->id)),'Edit',array('class'=>'btn edit-client','title'=>'Edits a Registered Application')) }}
                            {{ HTML::link(URL::action("ClientApiController@delete",array("id"=>$client->id)),'Delete',array('class'=>'btn del-client','title'=>'Deletes a Registered Application')) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    <div id="dialog-form-register-new-app" title="Register new Application">
        <p style="font-size: 10px;">* You need to register your application to get the necessary credentials to call a Openstack API</p>
        <form id="form-register-new-app" name="form-register-new-app">
            <fieldset>
                <label for="app_name">Application Name</label>
                <input type="text" name="app_name" id="app_name">

                <label for="app_desc">Application Description</label>
                <textarea style="resize: none;" rows="4" cols="50" name="app_desc" id="app_desc"></textarea>
                <label for="app_type">Application Type</label>
                <select name="app_type" id="app_type">
                    <option value="2">Web Application</option>
                    <option value="1">Browser (JS Client)</option>
                    <option value="1">Native Application</option>
                </select>
            </fieldset>
        </form>
    </div>



    @if (count($actions)>0)
    <div class="row-fluid">
        <div id="actions" class="span12">
            <legend><i class="icon-info-sign accordion-toggle" title="Users actions"></i>&nbsp;User Actions</legend>
            <table class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th>From Realm</th>
                    <th>Action</th>
                    <th>From IP</th>
                    <th><i class="icon-info-sign accordion-toggle" title="Time is on UTC"></i>&nbsp;When</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($actions as $action)
                <tr>
                    @if(is_null($action->realm))
                    <td>Site</td>
                    @else
                    <td>{{ $action->realm }}</td>
                    @endif
                    <td>{{ $action->user_action }}</td>
                    <td>{{ $action->from_ip }}</td>
                    <td>{{ $action->created_at }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
<div class="span5">
</div>

@stop

@section('scripts')
<script type="application/javascript">

    function loadClients(){
        $.ajax(
            {
                type: "GET",
                url: '{{ URL::action("ClientApiController@getByPage",array("user_id"=>$user_id,"page_nbr"=>1,"page_size"=>1000)) }}',
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
                                'td.client-type':'client.client_type',
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
                                    var href = '{{ URL::action("UserController@getEditRegisteredClient",array("id"=>-1)) }}';
                                    return href.replace('-1',client_id);
                                },
                                'a.del-client@href':function(arg){
                                    var client_id = arg.item.id;
                                    var href = '{{ URL::action("ClientApiController@delete",array("id"=>-1)) }}';
                                    return href.replace('-1',client_id);
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
                    var HTTP_status = jqXHR.status;
                    response = $.parseJSON(jqXHR.responseText);
                    alert('server error');
                }
            }
        );
    }

    $(document).ready(function() {

        var application_form = $('#form-register-new-app');

        var application_validator = application_form.validate({
                rules: {
                    "app_name" : {required: true, nowhitespace:true,rangelength: [1, 255]},
                    "app_desc" : {required: true, letterswithbasicpunc:true,rangelength: [1, 512]}
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

            $("#dialog-form-register-new-app").dialog({
                autoOpen: false,
                height: 500,
                width: 455,
                modal: true,
                close: function( event, ui ) {
                    application_form.cleanForm();
                    application_validator.resetForm();
                },
                buttons: {
                "Register": function() {

                    var is_valid        = application_form.valid();
                    if (!is_valid) return;
                    var application     = application_form.serializeForm();
                    application.active  = true;
                    application.user_id = {{$user_id}};

                    $(this).dialog( "close" );

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
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            ajaxError(jqXHR, textStatus, errorThrown);
                        }
                    });
                 },
                 Cancel: function() {
                    $( this ).dialog( "close" );
                 }
                }
        });

        $("body").on('click',".add-client",function(event){
            var link = $(this).attr('href');
            event.preventDefault();
            $("#dialog-form-register-new-app").dialog( "open" );
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
                            var HTTP_status = jqXHR.status;
                            if(HTTP_status!=200){
                                response = $.parseJSON(jqXHR.responseText);
                                alert(response.error);
                            }
                            else{
                                alert('server error');
                            }
                        }
                    }
                );
            }
            event.preventDefault();
            return false;
        });


        $("body").on('click',".app-active-checkbox",function(event){

            var client    = {};
            var client_id = $(this).attr('value');
            var url       = '{{ URL::action("ClientApiController@updateStatus",array("id"=>-1)) }}'
            url           = url.replace('-1',client_id);
            client.active = $(this).is(':checked');
            $.ajax(
                {
                    type: "PUT",
                    url: url,
                    data: JSON.stringify(client),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        var HTTP_status = jqXHR.status;
                        response = $.parseJSON(jqXHR.responseText);
                        alert('server error');
                    }
                }
            );
        });

    });
</script>
@stop

