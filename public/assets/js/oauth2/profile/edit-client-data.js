jQuery(document).ready(function($){

    $('#contacts').tagsinput({
        trimValue: true,
        onTagExists: function(item, $tag) {
            $tag.hide().fadeIn();
        },
        allowDuplicates: false
    });

    $('#contacts').on('beforeItemAdd', function(event) {
        // event.item: contains the item
        // event.cancel: set to true to prevent the item getting added
        var regex_email = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/ig;
        var current = regex_email.test( event.item );
        if(!current)
            event.cancel = true;
    });

    var users = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: dataClientUrls.fetchUsers+'?t=%QUERY',
            wildcard: '%QUERY'
        }
    });

    $('#admin_users').tagsinput({
        itemValue: 'id',
        itemText: 'value',
        freeInput: false,
        allowDuplicates: false,
        trimValue: true,
        typeaheadjs: [
            {
                hint: true,
                highlight: true,
                minLength: 1
            },
            {
                name: 'users',
                displayKey: 'value',
                source: users
            }
        ]
    });

    for(var user of current_admin_users)
    {
        $('#admin_users').tagsinput('add',user);
    }

    $('#redirect_uris').tagsinput({
        trimValue: true,
        onTagExists: function(item, $tag) {
            $tag.hide().fadeIn();
        },
        allowDuplicates: false
    });

    $('#redirect_uris').on('beforeItemAdd', function(event) {
        var uri       = new URI(event.item);
        var app_type  = $('#application_type').val();
        var valid     = app_type == 'NATIVE' ? true : uri.protocol() === 'https' ;
        var valid     = valid && uri.is('url') && uri.is('absolute') && uri.search() == '' && uri.fragment() == ''
        if(!valid)
            event.cancel = true;
    });

    $('#allowed_origins').tagsinput({
        trimValue: true,
        onTagExists: function(item, $tag) {
            $tag.hide().fadeIn();
        },
        allowDuplicates: false
    });

    $('#allowed_origins').on('beforeItemAdd', function(event) {

        var uri       = new URI(event.item);
        var valid     = uri.is('url') && uri.is('absolute') && uri.protocol() === 'https' && uri.search() == '' && uri.fragment() == '' ;
        if(!valid)
            event.cancel = true;
    });

    $("body").on('click',".regenerate-client-secret",function(event){
        var link = $(this).attr('href');
        swal({
            title: "Are you sure?",
            text: "Regenerating client secret would invalidate all current tokens!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, Regenerate it!",
            closeOnConfirm: true
        },
        function(){
            $.ajax(
                {
                    type: "PUT",
                    url: link,
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                        $('#client_secret').text(data.new_secret);
                        $('#client_secret_expiration_date').text(data.new_expiration_date.date);
                        //clean token UI
                        $('#table-access-tokens').remove();
                        $('#table-refresh-tokens').remove();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        ajaxError(jqXHR, textStatus, errorThrown);
                    }
                }
            );
        });
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

    var form = $('#form-application-main-data');

    var validator = form.validate({
        rules: {
            website: {url: true},
            logo_uri: {url: true},
            tos_uri: {url: true},
            policy_uri: {url: true},
            app_description : {required: true, free_text:true,rangelength: [1, 512]},
        }
    });

    form.submit(function(e){
        var is_valid = $(this).valid();
        if (is_valid) {

            var application_data = $(this).serializeForm();

            $.ajax(
                {
                    type: "PUT",
                    url: dataClientUrls.update + '?client_id=' + application_data.id,
                    data: JSON.stringify(application_data),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    timeout: 60000,
                    success: function (data, textStatus, jqXHR) {
                        displaySuccessMessage('Data saved successfully.', form);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        ajaxError(jqXHR, textStatus, errorThrown);
                    }
                }
            );
        }
        e.preventDefault();
        return false;
    });

});