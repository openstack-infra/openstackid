(function( $ ){
    $.fn.serializeForm = function() {
        var o           = {};
        var a           = this.serializeArray();
        var $form       = $(this);

        $.each(a, function() {
            //special case for checkboxes
            var $element = $('#'+this.name,$form);
            if($element.is(':checkbox')){
                this.value = $element.is(':checked');
            }
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        //add missing checkboxes (non checked ones)
        $('input:checkbox',$form).each(function() {
            var id    =  $(this).attr('id');
            var value =  $(this).is(':checked');
            if (!o.hasOwnProperty(id))
                o[id] =  value;
        });
        return o;
    };
}( jQuery ));