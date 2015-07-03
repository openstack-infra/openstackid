jQuery(document).ready(function($){

    var form = $("#form-application-security");

    $.validator.addMethod("must_use_alg", function(value, element, options) {
        if(value === 'none') return true;
        return $(options.alg_element_id).val() !== 'none';
    },"You must select an Encrypted Key Algorithm");

    $.validator.addMethod("must_use_enc", function(value, element, options) {
        if(value === 'none') return true;
        return $(options.enc_element_id).val() !== 'none';
    },"You must select an Encrypted Content Algorithm");


    var validator = form.validate({
        rules: {
            "default_max_age"                 : {integer : true},
            "jwks_uri"                        : {ssl_uri : true},
            "userinfo_encrypted_response_enc" : { must_use_alg: {alg_element_id:'#userinfo_encrypted_response_alg'}},
            "id_token_encrypted_response_enc" : { must_use_alg: {alg_element_id:'#id_token_encrypted_response_alg'}},
            "userinfo_encrypted_response_alg" : { must_use_enc: {enc_element_id:'#userinfo_encrypted_response_enc'}},
            "id_token_encrypted_response_alg" : { must_use_enc: {enc_element_id:'#id_token_encrypted_response_enc'}}
        }
    });

    $('#token_endpoint_auth_method').change(function() {
        var auth_method = $(this).val();

        if(auth_method === 'private_key_jwt' || auth_method === 'client_secret_jwt')
        {
            var signing_alg_select = $('#token_endpoint_auth_signing_alg')
            $('#token_endpoint_auth_signing_alg_group').show();
            signing_alg_select.empty();
            var result = [];

            if(auth_method === 'private_key_jwt')
            {
                result = oauth2_supported_algorithms.sig_algorihtms.rsa;
            }
            else
            {
                result = oauth2_supported_algorithms.sig_algorihtms.mac;
            }

            $.each(result, function(index, item) {
                var key = item === 'none' ? '' : item;
                signing_alg_select.append($("<option />").val(key).text(item));
            });
        }
        else
        {
            $('#token_endpoint_auth_signing_alg_group').hide();
        }
    });

    $('#token_endpoint_auth_method').trigger('change');

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
