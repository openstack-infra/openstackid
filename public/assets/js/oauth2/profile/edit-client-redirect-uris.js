function loadAllowedClientUris(){
    var link = clientUrisUrls.get;
    $.ajax(
        {
            type: "GET",
            url: link,
            dataType: "json",
            timeout:60000,
            success: function (data,textStatus,jqXHR) {
                //load data...
                var uris = data.allowed_uris;
                if(uris.length>0){
                    var template = $('<tbody><tr><td class="uri-text"></td><td><a title="Deletes a Allowed Uri" class="btn del-allowed-uri">Delete</a></td></tr></tbody>');
                    var directives = {
                        'tr':{
                            'uri<-context':{
                                'td.uri-text':'uri.uri',
                                'a.del-allowed-uri@href':function(arg){
                                    var uri_id = arg.item.id;
                                    var href = clientUrisUrls.delete;
                                    return href.replace('-1',uri_id);
                                }

                            }
                        }
                    };
                    var html = template.render(uris, directives);
                    $('#body-allowed-uris').html(html.html());
                    $('#info-uris').hide();
                    $('#table-uris').show();
                }
                else{
                    $('#info-uris').show();
                    $('#table-uris').hide();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                ajaxError(jqXHR, textStatus, errorThrown);
            }
        }
    );
}

jQuery(document).ready(function($){

    if($('#table-uris tr').length===1){
        $('#info-uris').show();
        $('#table-uris').hide();
    }
    else{
        $('#info-uris').hide();
        $('#table-uris').show();
    }

    var form_add_redirect_uri = $('#form-add-uri');

    var add_redirect_uri_validator = form_add_redirect_uri.validate({
        rules: {"redirect_uri"  :{required: true, ssl_uri: true}}
    });

    $("body").on('click',".add-uri-client",function(event){
        var is_valid = form_add_redirect_uri.valid();
        if (is_valid){
            var link = $(this).attr('href');
            var uri = form_add_redirect_uri.serializeForm();
            form_add_redirect_uri.cleanForm();
            add_redirect_uri_validator.resetForm();
            $.ajax({
                type: "POST",
                url: link,
                dataType: "json",
                data: JSON.stringify(uri),
                contentType: "application/json; charset=utf-8",
                timeout:60000,
                success: function (data,textStatus,jqXHR) {
                    $('#redirect_uri').val('');
                    loadAllowedClientUris();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    ajaxError(jqXHR, textStatus, errorThrown);
                }
            });
        }
        event.preventDefault();
        return false;
    });

    $("body").on('click',".del-allowed-uri",function(event){
        if(confirm("Are you sure?")){
            var link = $(this).attr('href');
            $.ajax({
                    type: "DELETE",
                    url: link,
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        loadAllowedClientUris();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        ajaxError(jqXHR, textStatus, errorThrown);
                    }
            });
        }
        event.preventDefault();
        return false;
    });
});