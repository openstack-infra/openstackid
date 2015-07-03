
function loadAllowedClientOrigin() {
    var link = clientOriginsUrls.get;
    $.ajax(
        {
            type: "GET",
            url: link,
            dataType: "json",
            timeout:60000,
            success: function (data,textStatus,jqXHR) {
                //load data...

                var origins = data.allowed_origins;
                if(origins.length>0){
                    var template = $('<tbody><tr><td class="origin-text"></td><td><a title="Deletes a Allowed Origin" class="btn del-allowed-origin">Delete</a></td></tr></tbody>');
                    var directives = {
                        'tr':{
                            'origin<-context':{
                                'td.origin-text':'origin.allowed_origin',
                                'a.del-allowed-origin@href':function(arg){
                                    var origin_id = arg.item.id;
                                    var href = clientOriginsUrls.delete;
                                    return href.replace('@id',origin_id);
                                }

                            }
                        }
                    };
                    var html = template.render(origins, directives);
                    $('#body-allowed-origins').html(html.html());
                    $('#info-origins').hide();
                    $('#table-origins').show();
                }
                else{
                    $('#info-origins').show();
                    $('#table-origins').hide();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                ajaxError(jqXHR, textStatus, errorThrown);
            }
        }
    );
}

jQuery(document).ready(function($){

    if($('#table-origins tr').length === 1){
        $('#info-origins').show();
        $('#table-origins').hide();
    }
    else{
        $('#info-origins').hide();
        $('#table-origins').show();
    }

    var form_add_origin = $('#form-add-origin');

    var add_origin_validator = form_add_origin.validate({
        rules: {
            "origin"  :{required: true, ssl_uri: true}
        }
    });

    $("body").on('click',".add-origin-client",function(event){
        var is_valid = form_add_origin.valid();
        if (is_valid){
            var link = $(this).attr('href');
            var origin = form_add_origin.serializeForm();
            form_add_origin.cleanForm();
            add_origin_validator.resetForm();
            $.ajax({
                type: "POST",
                url: link,
                dataType: "json",
                data: JSON.stringify(origin),
                contentType: "application/json; charset=utf-8",
                timeout:60000,
                success: function (data,textStatus,jqXHR) {
                    $('#origin').val('');
                    loadAllowedClientOrigin();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    ajaxError(jqXHR, textStatus, errorThrown);
                }
            });
        }
        event.preventDefault();
        return false;
    });

    $("body").on('click',".del-allowed-origin",function(event){

        if(confirm("Are you sure?")){
            var link = $(this).attr('href');
            $.ajax(
                {
                    type: "DELETE",
                    url: link,
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        loadAllowedClientOrigin();
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