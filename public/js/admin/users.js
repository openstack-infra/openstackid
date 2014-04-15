$(document).ready(function() {
    $('#server-admin','#main-menu').addClass('active');

    if($('#users-table tr').length===1){
        $('#users-info').show();
        $('#users-table').hide();
    }
    else{
        $('#users-info').hide();
        $('#users-table').show();
    }

    $("body").on('click',".unlock-user",function(event){
        if(confirm("Are you sure that you want to unlock this User?")){
            var url = $(this).attr('href');
            var user_id = $(this).attr('data-user-id');
            $.ajax(
                {
                    type: "DELETE",
                    url: url,
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                        $('#'+user_id,'#body-locked-users').remove();

                        if($('#users-table tr').length===1){
                            $('#users-info').show();
                            $('#users-table').hide();
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