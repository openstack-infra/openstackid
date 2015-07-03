jQuery(document).ready(function($){

    $("body").on('click',".regenerate-client-secret",function(event){
        if(confirm("Are you sure? Regenerating client secret would invalidate all current tokens")){
            var link = $(this).attr('href');
            $.ajax(
                {
                    type: "PUT",
                    url: link,
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                        $('#client_secret').text(data.new_secret);
                        //clean token UI
                        $('#table-access-tokens').remove();
                        $('#table-refresh-tokens').remove();
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

    $("body").on('click',"#use-refresh-token",function(event){
        var param = {};
        param.use_refresh_token  = $(this).is(':checked');
        $.ajax(
            {
                type: "PUT",
                url: dataClientUrls.refresh,
                data: JSON.stringify(param),
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                timeout:60000,
                success: function (data,textStatus,jqXHR) {
                    //load data...
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    ajaxError(jqXHR, textStatus, errorThrown);
                }
            }
        );
    });

    $("body").on('click',"#use-rotate-refresh-token-policy",function(event){
        var param = {};
        param.rotate_refresh_token  = $(this).is(':checked');
        $.ajax(
            {
                type: "PUT",
                url: dataClientUrls.rotate,
                data: JSON.stringify(param),
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                timeout:60000,
                success: function (data,textStatus,jqXHR) {
                    //load data...
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    ajaxError(jqXHR, textStatus, errorThrown);
                }
            }
        );
    });
});