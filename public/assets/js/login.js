jQuery(document).ready(function($){

   $('#login_form').submit(function(){
        $('.btn-primary').attr('disabled', 'disabled');
        return true;
   });

});