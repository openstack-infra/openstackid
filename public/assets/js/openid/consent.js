jQuery(document).ready(function($){

    $("body").on('click',"#cancel_authorization",function(event){
        var $form = $('#authorization_form');
        $("#deny_once").prop("checked", true)
        $form.submit();
        event.preventDefault();
        return false;
    });

    $('#authorization_form').submit(function(){
        $('.btn-consent-action').attr('disabled', 'disabled');
        return true;
    })
});