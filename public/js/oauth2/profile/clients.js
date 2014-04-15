
function loadClients(){
    $.ajax({
            type: "GET",
            url: clientsUrls.load,
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
                                return arg.item.active?'true':'';
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
                                return arg.item.locked?'true':'';
                            },
                            'a.edit-client@href':function(arg){
                                var client_id = arg.item.id;
                                var href = clientsUrls.edit;
                                return href.replace('@id',client_id);
                            },
                            'a.del-client@href':function(arg){
                                var client_id = arg.item.id;
                                var href = clientsUrls.delete;
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
     });
}

jQuery(document).ready(function($){

    $('#oauth2-console','#main-menu').addClass('active');

    var application_form      = $('#form-application');
    var application_dialog    = $("#dialog-form-application");
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
            application.user_id = userId;
            var link = $(this).attr('href');
            $.ajax({
                type: "POST",
                url: clientsUrls.add,
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
        var active    = $(this).is(':checked');
        var client_id = $(this).attr('value');
        var url       = active? clientsUrls.activate : clientsUrls.deactivate;
        url           = url.replace('@id',client_id);
        var verb      = active?'PUT':'DELETE';

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
});