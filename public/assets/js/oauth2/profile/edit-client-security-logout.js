jQuery(document).ready(function($){

    var form = $("#form-application-security-logout");

    $('#post_logout_redirect_uris').tagsinput({
        trimValue: true,
        onTagExists: function(item, $tag) {
            $tag.hide().fadeIn();
        },
        allowDuplicates: false
    });

    $('#post_logout_redirect_uris').on('beforeItemAdd', function(event) {
        // event.item: contains the item
        // event.cancel: set to true to prevent the item getting added
        var regex   = /^https:\/\/([\w@][\w.:@]+)\/?[\w\.?=%&=\-@/$,]*$/ig;
        var current = regex.test(event.item);
        if(!current)
            event.cancel = true;
    });

    var validator = form.validate({
            rules: {
                "logout_uri"  : {ssl_uri : true}
            }
    });

    form.submit(function(e){
        var is_valid = $(this).valid();
        if (is_valid) {

            var application_data = $(this).serializeForm();

            $.ajax(
                {
                    type: "PUT",
                    url: dataClientUrls.update + '?client_id=' + application_data.id,
                    data: JSON.stringify(application_data),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    timeout: 60000,
                    success: function (data, textStatus, jqXHR) {
                        displaySuccessMessage('Data saved successfully.', form);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        ajaxError(jqXHR, textStatus, errorThrown);
                    }
                }
            );
        }
        e.preventDefault();
        return false;
    });

});