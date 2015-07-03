(function( $ ){
    $.fn.cleanForm = function() {
        //after serialize clear all!
        $('input',this).val('');
        $('select',this).val('');
        $('textarea',this).val('');
        this.find(':checkbox').removeAttr('checked');
        return this;
    };
}( jQuery ));