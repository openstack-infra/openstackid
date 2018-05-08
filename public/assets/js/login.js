jQuery(document).ready(function($){

   $('#login_form').submit(function(event){
        if(!navigator.cookieEnabled){
            event.preventDefault();
            checkCookiesEnabled();
            return false;
        }
        $('.btn-primary').attr('disabled', 'disabled');
        return true;
   });

    checkCookiesEnabled();
});

function checkCookiesEnabled(){
    var cookieEnabled = navigator.cookieEnabled;

    return cookieEnabled || showCookieFail();
}

function showCookieFail(){
    $('#cookies-disabled-dialog').show();
}