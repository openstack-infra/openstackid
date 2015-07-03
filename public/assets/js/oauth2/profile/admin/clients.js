jQuery(document).ready(function($){

    $('#server-admin','#main-menu').addClass('active');

    if($('#clients-table tr').length===1){
        $('#clients-info').show();
        $('#clients-table').hide();
    }
    else{
        $('#clients-info').hide();
        $('#clients-table').show();
    }

    $("body").on('click',".unlock-client",function(event){
        if(confirm("Are you sure that you want to unlock this OAUTH2 Client?")){
            var url = $(this).attr('href');
            var client_id = $(this).attr('data-client-id');
            $.ajax(
                {
                    type: "DELETE",
                    url: url,
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                        $('#'+client_id,'#body-locked-clients').remove();

                        if($('#clients-table tr').length===1){
                            $('#clients-info').show();
                            $('#clients-table').hide();
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        ajaxError(jqXHR, textStatus, errorThrown);
                    }
                }
            );
        }
        event.preventDefault();
        return false;
    });
});