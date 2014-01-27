
jQuery.validator.addMethod("ipV4", function(value, element) {
    return this.optional(element) || /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/i.test(value);
}, "please enter a valid IPV4 address");


jQuery.validator.addMethod("ssl_uri", function(value, element) {
    var regex = /^https:\/\/([\w@][\w.:@]+)\/?[\w\.?=%&=\-@/$,]*$/ig;
    return this.optional(element) || regex.test(value);
}, "uri must under https schema.");


jQuery.validator.addMethod("scopename", function(value, element) {
    var regex = /^[a-zA-Z0-9\-\.\,\:\_\/]+$/ig;
    return this.optional(element) || regex.test(value);
}, "please enter a valid scope name.");

jQuery.validator.addMethod("free_text", function(value, element) {
    return this.optional(element) || /^[a-z\-.,()'"\s\/]+$/i.test(value);
}, "Letters or punctuation only please");


var showCustomLabel = function ( element, message ) {
    var label = this.errorsFor( element );
    if ( label.length ) {
        // refresh error/success class
        label.removeClass( this.settings.validClass ).addClass( this.settings.errorClass );
        // replace message on existing label
        $('.text',label).html(message || "")
    } else {
        // create label
        var label = $('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><span class="text"></span></div>');
        $('.text',label).html(message || "")
        label.attr('for',this.idOrName(element)).addClass(this.settings.errorClass);

        if ( this.settings.wrapper ) {
             // make sure the element is visible, even in IE
            // actually showing the wrapped element is handled elsewhere
            label = label.hide().show().wrap("<" + this.settings.wrapper + "/>").parent();
        }
        if ( !this.labelContainer.append(label).length ) {
            if ( this.settings.errorPlacement ) {
                this.settings.errorPlacement(label, $(element) );
            } else {
                label.insertAfter(element);
            }
        }
    }
    if ( !message && this.settings.success ) {
        label.text("");
        if ( typeof this.settings.success === "string" ) {
            label.addClass( this.settings.success );
        } else {
            this.settings.success( label, element );
        }
    }
    this.toShow = this.toShow.add(label);
};


jQuery.validator.setDefaults({
    showErrors: function(errorMap, errorList) {
        var i, elements;
        for ( i = 0; this.errorList[i]; i++ ) {
            var error = this.errorList[i];
            if ( this.settings.highlight ) {
                this.settings.highlight.call( this, error.element, this.settings.errorClass, this.settings.validClass );
            }
            showCustomLabel.call( this,error.element, error.message );
            //this.showLabel( error.element, error.message );
        }
        if ( this.errorList.length ) {
            this.toShow = this.toShow.add( this.containers );
        }
        if ( this.settings.success ) {
            for ( i = 0; this.successList[i]; i++ ) {
                this.showLabel( this.successList[i] );
            }
        }
        if ( this.settings.unhighlight ) {
            for ( i = 0, elements = this.validElements(); elements[i]; i++ ) {
                this.settings.unhighlight.call( this, elements[i], this.settings.errorClass, this.settings.validClass );
            }
        }
        this.toHide = this.toHide.not( this.toShow );
        this.hideErrors();
        this.addWrapper( this.toShow ).show();
    },
    errorElement:'div',
    highlight: function(element) {
        $(element).addClass('error');
    },
    unhighlight: function(element) {
        $(element).removeClass("error");
    },
    focusCleanup:true,
    invalidHandler: function(form, validator) {
        var errors = validator.numberOfInvalids();
        if (errors) {
            validator.errorList[0].element.focus();
        }
    }
});