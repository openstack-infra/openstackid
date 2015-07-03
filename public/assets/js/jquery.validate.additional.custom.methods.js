
jQuery.validator.addMethod("ipV4", function(value, element) {
    return this.optional(element) || /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/i.test(value);
}, "Please enter a valid IPV4 address");


jQuery.validator.addMethod("ssl_uri", function(value, element) {
    var regex = /^https:\/\/([\w@][\w.:@]+)\/?[\w\.?=%&=\-@/$,]*$/ig;
    return this.optional(element) || regex.test(value);
}, "Uri must under https schema.");


jQuery.validator.addMethod("scopename", function(value, element) {
    var regex = /^[a-zA-Z0-9\-\.\,\:\_\/]+$/ig;
    return this.optional(element) || regex.test(value);
}, "Please enter a valid scope name.");

jQuery.validator.addMethod("free_text", function(value, element) {
    return this.optional(element) || /^[a-z\-_.,()'"\s\:\/]+$/i.test(value);
}, "Letters or punctuation only please");

jQuery.validator.addMethod("endpointroute", function(value, element) {
    return this.optional(element) || /^\/([\w@][\w.:@]+)\/?[\w\.?=%&=\-@/$,{}]*$/ig.test(value);
}, "Please enter a valid endpoint route");


// override jquery validate plugin defaults
$.validator.setDefaults({
    ignore: [],
    highlight: function(element) {
        $(element).closest('.form-group').addClass('has-error');
    },
    unhighlight: function(element) {
        $(element).closest('.form-group').removeClass('has-error');
    },
    errorElement: 'span',
    errorClass: 'help-block',
    errorPlacement: function(error, element) {
        if(element.parent('.input-group').length) {
            error.insertAfter(element.parent());
        } else {
            error.insertAfter(element);
        }
    }
});