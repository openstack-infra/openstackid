$(document).ready(function() {

    $('#server-admin','#main-menu').addClass('active');

    var scope_form = $('#scope-form');

    var scope_validator = scope_form.validate({
        rules: {
            "name"  :              {required: true, scopename:true,rangelength: [1, 512]},
            "short_description":   {required: true, free_text:true,rangelength: [1, 512]},
            "description":         {required: true, free_text:true,rangelength: [1, 1024]}
        }
    });

    scope_form.submit(function( event ) {
        var is_valid = scope_form.valid();
        if (is_valid){
            scope_validator.resetForm();
            var scope = scope_form.serializeForm();
            var href = $(this).attr('action');
            $.ajax(
                {
                    type: "PUT",
                    url: href,
                    data: JSON.stringify(scope),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        displaySuccessMessage(editScopeMessages.success,scope_form);
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
