function loadGroups(){
    var link = ApiScopeGroupUrls.get;
    $.ajax(
        {
            type: "GET",
            url: link,
            dataType: "json",
            timeout:60000,
            success: function (data,textStatus,jqXHR) {
                //load data...
                var groups     = data.page;

                if(groups.length > 0) {
                    var template = $('<tbody><tr><td class="name"></td><td class="group-active"><input type="checkbox" class="api-scope-group-active-checkbox"></td><td>&nbsp;<a class="btn btn-default active edit-api-scope-group" title="Edit a Registered Group">Edit</a>&nbsp;<a class="btn btn-default btn-delete active delete-api-scope-group" title="Deletes a Registered Group">Delete</a></td></tr></tbody>');
                    var directives = {
                        'tr': {
                            'group<-context': {
                                'td.name': 'group.name',
                                '.api-scope-group-active-checkbox@value': 'group.id',
                                '.api-scope-group-active-checkbox@checked': function (arg) {
                                    return arg.item.active ? 'true' : '';
                                },
                                '.api-scope-group-active-checkbox@data-group-id': 'group.id',
                                '.api-scope-group-active-checkbox@id': function (arg) {
                                    var id = arg.item.id;
                                    return 'api-scope-group-active_' + id;
                                },
                                'a.edit-api-scope-group@href': function (arg) {
                                    var id = arg.item.id;
                                    var href = ApiScopeGroupUrls.edit;
                                    return href.replace('-1', id);
                                },
                                'a.delete-api-scope-group@href': function (arg) {
                                    var id = arg.item.id;
                                    var href = ApiScopeGroupUrls.delete;
                                    return href.replace('-1', id);
                                }
                            }
                        }
                    };
                    var html = template.render(groups, directives);
                    $('#body-api-scope-groups').html(html.html());
                    $('#info-api-scope-groups').hide()
                }
                else
                {
                    $('#info-api-scope-groups').show();
                    $('#body-api-scope-groups').hide();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                ajaxError(jqXHR, textStatus, errorThrown);
            }
        }
    );
}

$(document).ready(function() {

    $('#server-admin', '#main-menu').addClass('active');

    //validation rules on new server form
    var scope_group_form   = $('#form-api-scope-group');
    var dialog_scope_group = $('#dialog-form-api-scope-group');

    var scope_group_validator = scope_group_form.validate({
        rules: {
            "name"   : { required: true, free_text:true,rangelength: [1, 255]},
            "users"  : { required: true },
            "scopes" : { required: true },
        }
    });

    dialog_scope_group.modal({
        show:false,
        backdrop:"static"
    });

    dialog_scope_group.on('hidden.bs.modal', function (e) {
        scope_group_form.cleanForm();
        scope_group_validator.resetForm();
        $('#users').tagsinput('removeAll');
        $('#scopes').tagsinput('removeAll');
    })

    $("body").on('click',".add-api-scope-group",function(event){
        dialog_scope_group.modal('show');
        event.preventDefault();
        return false;
    });

    $("body").on('click',".refresh-groups",function(event){
        loadGroups()
        event.preventDefault();
        return false;
    });

    $("body").on('click',".api-scope-group-active-checkbox",function(event){
        var active   = $(this).is(':checked');
        var group_id = $(this).attr('data-group-id');
        var url      = active? ApiScopeGroupUrls.activate : ApiScopeGroupUrls.deactivate;
        url          = url.replace('@id',group_id);
        var verb     = active?'PUT':'DELETE';
        $.ajax(
            {
                type: verb,
                url: url,
                contentType: "application/json; charset=utf-8",
                success: function (data,textStatus,jqXHR) {
                    //load data...
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    ajaxError(jqXHR, textStatus, errorThrown);
                }
            }
        );
    });

    $("body").on('click',".save-api-scope-group",function(event){
        var is_valid = scope_group_form.valid();
        if (is_valid){
            var api_scope_group = scope_group_form.serializeForm();
            $.ajax({
                type: "POST",
                url: ApiScopeGroupUrls.add,
                data: JSON.stringify(api_scope_group),
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                timeout:60000,
                success: function (data,textStatus,jqXHR) {
                    loadGroups();
                    dialog_scope_group.modal('hide');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    ajaxError(jqXHR, textStatus, errorThrown);
                }
            });
        }
        event.preventDefault();
        return false;
    });

    $("body").on('click',".delete-api-scope-group",function(event){
        if(confirm("Are you sure?")){
            var href = $(this).attr('href');
            $.ajax(
                {
                    type: "DELETE",
                    url: href,
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        loadGroups();
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

    // modal controls

    var scopes = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        local: all_scopes
    });

    $('#scopes').tagsinput({
        itemValue: 'id',
        itemText: 'value',
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

    var users = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        local: all_users
    });

    $('#users').tagsinput({
        itemValue: 'id',
        itemText: 'value',
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

});