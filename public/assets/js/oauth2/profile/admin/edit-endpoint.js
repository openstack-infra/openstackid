jQuery(document).ready(function($){

    $('#server-admin','#main-menu').addClass('active');

    var endpoint_form = $('#endpoint-form');

    var endpoint_validator = endpoint_form.validate({
        rules: {
            "name"  :     {required: true, nowhitespace:true,rangelength: [1, 255]},
            "description":{required: true, free_text:true,rangelength: [1, 1024]},
            "route":      {required: true, endpointroute:true,rangelength: [1, 1024]},
            "rate_limit": {required: true, number:true}
        }
    });

    endpoint_form.submit(function( event ) {
        var is_valid = endpoint_form.valid();
        if (is_valid){
            endpoint_validator.resetForm();
            var endpoint = endpoint_form.serializeForm();
            var href     = $(this).attr('action');
            $.ajax(
                {
                    type: "PUT",
                    url: href,
                    data: JSON.stringify(endpoint),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        displaySuccessMessage(editEndpointMessages.success , endpoint_form);
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
    //scopes associations
    $("body").on('click',".scope-checkbox",function(event){
        var add_link = $(this).attr('data-add-link');
        var del_link = $(this).attr('data-remove-link');
        var checked  = $(this).is(':checked');
        var url      = checked?add_link:del_link;
        var verb     = checked?'PUT':'DELETE';
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
});