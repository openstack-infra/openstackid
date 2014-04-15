
function loadApis(){
    $.ajax({
        type: "GET",
        url: ApiUrls.get,
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
                                return arg.item.active?'true':'';
                            },
                            '.api-active-checkbox@id':function(arg){
                                var id = arg.item.id;
                                return 'resource-server-api-active_'+id;
                            },
                            '.api-active-checkbox@data-api-id':'api.id',
                            'a.edit-api@href':function(arg){
                                var id = arg.item.id;
                                var href = ApiUrls.edit;
                                return href.replace('-1',id);
                            },
                            'a.delete-api@href':function(arg){
                                var id = arg.item.id;
                                var href = ApiUrls.delete;
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

jQuery(document).ready(function($){

    $('#server-admin','#main-menu').addClass('active');

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
                url: ApiUrls.add,
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
                        displaySuccessMessage(resourceServerMessages.success , resource_server_form);
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
        var url    = active? ApiUrls.activate : ApiUrls.deactivate;
        url        = url.replace('@id',api_id);
        var verb   = active?'PUT':'DELETE';
        $.ajax(
            {
                type: verb,
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

    $("body").on('click',".regenerate-client-secret",function(event){
        if(confirm("Are you sure? Regenerating client secret would invalidate all current tokens")){
            var link = $(this).attr('href');
            $.ajax(
                {
                    type: "PUT",
                    url: link,
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                        $('#client_secret').text(data.new_secret);
                        //clean token UI
                        $('#table-access-tokens').remove();
                        $('#table-refresh-tokens').remove();
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