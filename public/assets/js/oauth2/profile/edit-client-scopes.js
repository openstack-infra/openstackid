jQuery(document).ready(function($){
    $("body").on('click',".scope-checkbox",function(event){
        var add_url    = clientScopesUrls.add;
        var remove_url = clientScopesUrls.delete;
        var scope_id   = $(this).attr('value');
        var checked    = $(this).is(':checked');
        var that       = $(this);
        var url        = checked?add_url:remove_url;
        url            = url.replace('@scope_id',scope_id);
        var verb       = checked ? 'PUT' : 'DELETE';
        $.ajax(
            {
                type: verb,
                url: url,
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                timeout:60000,
                success: function (data,textStatus,jqXHR) {
                    //load data...
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    ajaxError(jqXHR, textStatus, errorThrown);
                    that.attr('checked', false);
                }
            }
        );
    });
});