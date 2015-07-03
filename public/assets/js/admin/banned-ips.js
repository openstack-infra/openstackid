jQuery(document).ready(function($){

    $('#server-admin','#main-menu').addClass('active');

    if($('#ips-table tr').length===1){
        $('#ips-info').show();
        $('#ips-table').hide();
    }
    else{
        $('#ips-info').hide();
        $('#ips-table').show();
    }

    $("body").on('click',".revoke-ip",function(event){
        if(confirm("Are you sure that you want to revoke this banned ip?")){
            var url = $(this).attr('href');
            var ip_id = $(this).attr('data-ip-id');
            $.ajax(
                {
                    type: "DELETE",
                    url: url,
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                        $('#'+ip_id,'#body-ips').remove();

                        if($('#ips-table tr').length===1){
                            $('#ips-info').show();
                            $('#ips-table').hide();
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