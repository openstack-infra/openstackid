function loadScopes(){
    $.ajax({
        type: "GET",
        url: scopesUrls.get,
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
                                var href = scopesUrls.edit;
                                return href.replace('@id',id);
                            },
                            'a.delete-scope@href':function(arg){
                                var id = arg.item.id;
                                var href = scopesUrls.delete;
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
        url: endpointUrls.get,
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
                                var href = endpointUrls.edit;
                                return href.replace('@id',id);
                            },
                            'a.delete-endpoint@href':function(arg){
                                var id = arg.item.id;
                                var href = endpointUrls.delete;
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
                        displaySuccessMessage(editApiMessages.success,api_form);
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

        var url       = active? scopesUrls.activate : scopesUrls.deactivate;
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
                url: scopesUrls.update,
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
                url: scopesUrls.update,
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
                    url: scopesUrls.add,
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
        var url    = active? endpointUrls.activate : endpointUrls.deactivate;
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
            "route":      {required: true,endpointroute:true,rangelength: [1, 1024]},
            "rate_limit": {required: true, number:true}
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
                    url: endpointUrls.add,
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