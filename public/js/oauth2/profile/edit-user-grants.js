jQuery(document).ready(function($){

    $('#oauth2-console','#main-menu').addClass('active');

    if($('#table-access-tokens tr').length===1){
        $('#info-access-tokens').show();
        $('#table-access-tokens').hide();
    }
    else{
        $('#info-access-tokens').hide();
        $('#table-access-tokens').show();
    }

    if($('#table-refresh-tokens tr').length===1){
        $('#info-refresh-tokens').show();
        $('#table-refresh-tokens').hide();
    }
    else{
        $('#info-refresh-tokens').hide();
        $('#table-refresh-tokens').show();
    }

    $("body").on('click',".revoke-access",function(event){
        if(confirm("Are you sure to revoke this grant?")){
            var url   = $(this).attr('href');
            var value = $(this).attr('data-value');
            var hint  = $(this).attr('data-hint');
            var body  = hint=='access_token'?'body-access-tokens':'body-refresh-tokens'
            var table = hint=='access_token'?'table-access-tokens':'table-refresh-tokens'
            var info  = hint=='access_token'?'info-access-tokens':'info-refresh-tokens'
            $.ajax(
                {
                    type: "DELETE",
                    url: url,
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                        $('#'+value,'#'+body).remove();

                        if($('#'+table+' tr').length===1){
                            $('#'+info).show();
                            $('#'+table).hide();
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