jQuery(document).ready(function($){

    $("body").on('click',"#cancel_authorization",function(event){
        $form = $('#authorization_form');
        $("#deny_once").prop("checked", true)
        $form.submit();
        event.preventDefault();
        return false;
    });
});