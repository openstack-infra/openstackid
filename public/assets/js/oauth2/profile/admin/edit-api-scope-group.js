$(document).ready(function() {

    var scopes = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        local: all_scopes
    });

    $('#scopes').tagsinput({
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
                name: 'scopes',
                displayKey: 'value',
                source: scopes
            }
        ]
    });

    for(var scope of current_scopes)
    {
        $('#scopes').tagsinput('add',scope);
    }

    var users = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        local: all_users
    });

    $('#users').tagsinput({
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

    for(var user of current_users)
    {
        $('#users').tagsinput('add',user);
    }

    var group_form      = $('#api-scope-group-form');
    var group_validator = group_form.validate({
        rules: {
            "name"   : { required: true, free_text:true,rangelength: [1, 255]},
            "users"  : { required: true },
            "scopes" : { required: true },
        }
    });

    group_form.submit(function( event ) {

        var is_valid = group_form.valid();

        if (is_valid){
            group_validator.resetForm();
            var group = group_form.serializeForm();
            var href = $(this).attr('action');
            $.ajax(
                {
                    type: "PUT",
                    url: href,
                    data: JSON.stringify(group),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        displaySuccessMessage('Group Saved!.' , group_form);
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