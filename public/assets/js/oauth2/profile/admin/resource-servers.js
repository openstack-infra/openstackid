
function loadResourceServers(){
    var link = resourceServerUrls.get;
    $.ajax(
        {
            type: "GET",
            url: link,
            dataType: "json",
            timeout:60000,
            success: function (data,textStatus,jqXHR) {
                //load data...
                var uris = data.page;
                var template = $('<tbody><tr><td class="fname"></td><td class="hname"></td><td class="ip"></td><td class="resource-server-active"><input type="checkbox" class="resource-server-active-checkbox"></td><td>&nbsp;<a class="btn btn-default active edit-resource-server" title="Edits a Registered Resource Server">Edit</a>&nbsp;<a class="btn btn-default btn-delete active delete-resource-server" title="Deletes a Registered Resource Server">Delete</a></td></tr></tbody>');
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
                                var href = resourceServerUrls.edit;
                                return href.replace('-1',id);
                            },
                            'a.delete-resource-server@href':function(arg){
                                var id = arg.item.id;
                                var href = resourceServerUrls.delete;
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
        var url    = active? resourceServerUrls.activate : resourceServerUrls.deactivate;
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
                url: resourceServerUrls.add,
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
