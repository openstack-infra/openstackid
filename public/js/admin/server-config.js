jQuery(document).ready(function($){

    $('#server-admin','#main-menu').addClass('active');

    var form_server_config = $("#form-server-configuration");

    var server_config_validation = form_server_config.validate({
        rules: {
            "general-max-failed-login-attempts"           : {required: true, number: true },
            "general-max-failed-login-attempts-captcha"   : {required: true, number: true },
            "openid-private-association-lifetime"         : {required: true, number: true },
            "openid-session-association-lifetime"         : {required: true, number: true },
            "openid-nonce-lifetime"                       : {required: true, number: true },
            "oauth2-auth-code-lifetime"                   : {required: true, number: true },
            "oauth2-refresh-token-lifetime"               : {required: true, number: true },
            "oauth2-access-token-lifetime"                : {required: true, number: true }
        }
    });

    form_server_config.submit(function( event ) {
        var is_valid = form_server_config.valid();
        if (is_valid){
            server_config_validation.resetForm();
            return true;
        }
        event.preventDefault();
        return false;
    });
});
