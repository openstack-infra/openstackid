(function( $ ){

    $(document).ready(function($){

        var options = {};

        options.ui = {
            showPopover: false,
            showErrors: true,
            showProgressBar: true,
            showVerdictsInsideProgressBar: true,
        };

        options.rules = {
            activated: {
                wordTwoCharacterClasses: true,
                wordRepetitions: true
            }
        };
        $(':password').pwstrength(options);

        var modal = $('#ModalAddPrivateKey');
        modal.modal({show:false});

        $('#form-add-private-key .input-daterange').datepicker({
            startDate: "today",
            todayBtn: "linked",
            clearBtn: true,
            todayHighlight: true,
            orientation: "top right",
            autoclose: true
        });

        // private key form
        var form = $('#form-add-private-key');

        var validator = form.validate({
            rules: {
                "kid"  : {
                           required: true,
                           free_text : true,
                           maxlength:255,
                           minlength: 5
                },
                "valid_from": {
                    required: true,
                    dateUS:true
                },
                "valid_to": {
                    required: true ,
                    dateUS:true
                },
                "pem_content"  : { pem_private_key : function(element){
                    var autogenerate = $("#autogenerate").is(':checked');
                    return autogenerate? false: true;
                 }},
                 password: {
                             private_key_password_required: {pem_content_id: '#pem_content'},
                             minlength: 5
                           },
                "password-confirmation" : {
                             minlength: 5,
                             equalTo : "#password"
                },
            }
        });

        $("body").on('click',".add-private-key",function(event){
            modal.modal('show');
            validator.resetForm();
            $(":password").pwstrength("forceUpdate");
            $('#autogenerate').prop('checked', true);
            $('#active').prop('checked', true);
            $("#pem_container").hide();
            event.preventDefault();
            return false;
        });

        $("body").on('click',"#autogenerate",function(event){
            var autogenerate =  $(this).is(':checked');
            if(autogenerate){
                $("#pem_container").hide();
            }
            else{
                $("#pem_container").show();
            }
        });

        $("body").on('click',".delete-private-key",function(event){
            if(window.confirm('are you sure?')){
                //delete key
                var private_key_id = $(this).attr('data-private-key-id');

                $.ajax(
                    {
                        type: "DELETE",
                        url: privateKeyUrls.delete.replace('@id', private_key_id),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        timeout: 60000,
                        success: function (data, textStatus, jqXHR) {
                            $('#tr_'+private_key_id).fadeOut(300, function() {
                                $(this).remove();
                                if($('#body-private-keys').children('tr').length)
                                    $('.private-keys-empty-message').hide();
                                else
                                    $('.private-keys-empty-message').show();
                            });
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

        $("body").on('click',".save-private-key",function(event){

            if(form.valid()) {
                var private_key_data = form.serializeForm();
                private_key_data.type = 'RSA';

                if(private_key_data.autogenerate)
                     delete private_key_data.pem_content;

                $.ajax(
                    {
                        type: "POST",
                        url: privateKeyUrls.add,
                        data: JSON.stringify(private_key_data),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        timeout: 60000,
                        success: function (data, textStatus, jqXHR) {
                            modal.modal('hide');
                            form.cleanForm();

                            $('.private-keys-empty-message').hide();
                            loadPrivateKeys();
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

        $("body").on('click',".private-key-status",function(event){

            var status_badge       = $(this);
            var private_key_id      = status_badge.attr('data-private-key-id');
            var private_key_data    = { id : private_key_id };
            private_key_data.active = status_badge.hasClass('private-key-active') ? false : true;

            $.ajax(
                {
                    type: "PUT",
                    url: privateKeyUrls.update.replace('@id', private_key_id),
                    contentType: "application/json; charset=utf-8",
                    data: JSON.stringify(private_key_data),
                    dataType: "json",
                    timeout: 60000,
                    success: function (data, textStatus, jqXHR) {
                        if(private_key_data.active){
                            status_badge.removeClass('private-key-deactivated');
                            status_badge.addClass('private-key-active');
                            status_badge.attr('title', 'active');
                        }
                        else{
                            status_badge.removeClass('private-key-active');
                            status_badge.addClass('private-key-deactivated');
                            status_badge.attr('title', 'deactivated');
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        ajaxError(jqXHR, textStatus, errorThrown);
                    }
                }
            );

            event.preventDefault();
            return false;
        });

    });

    function loadPrivateKeys(){

        $.ajax({
                type: "GET",
                url: privateKeyUrls.get+'?offset=1&limit=4294967296',
                dataType: "json",
                timeout:60000,
                success: function (data,textStatus,jqXHR) {
                    //load data...
                    var private_keys = data.page;

                    if(private_keys.length > 0){

                        var template = $('<tbody>' +
                            '<tr>'+
                            '<td width="7%">'+
                            '<div class="row">'+
                            '<div class="col-md-6">'+
                            '<span class="badge private-key-status">&nbsp</span>'+
                            '</div>'+
                            '<div class="col-md-6 col-md-offset-neg-1">'+
                            '<i class="fa fa-key fa-2x pointable"></i>'+
                            '</div>'+
                            '</div>'+
                            '</td>'+
                            '<td colspan="3">'+
                            '<div class="row">'+
                            '<div class="col-md-12">'+
                            '<div class="row">'+
                            '<div class="col-md-12">'+
                            '<strong class="private-key-title"></strong>'+
                            '</div>'+
                            '</div>'+
                            '<div class="row">'+
                            '<div class="col-md-12">'+
                            '<code class="private-key-fingerprint"></code>'+
                            '</div>'+
                            '</div>'+
                            '<div class="row">'+
                            '<div class="col-md-12">'+
                            '<span class="private-key-validity-range"></span>'+
                            '</div>'+
                            '</div>'+
                            '</div>'+
                            '</div>'+
                            '</td>'+
                            '<td><a class="btn btn-default btn-sm active delete-private-key btn-delete" href="#">Delete</a></td>'+
                            '</tr>'+
                            '</tbody>');

                        var directives = {
                            'tr':{
                                'private_key<-context':{
                                    '.private-key-status@title':function(arg){
                                        return arg.item.active ? 'active': 'deactivated';
                                    },
                                    '.private-key-status@data-private-key-id':  'private_key.id',
                                    '.private-key-status@class+':function(arg){
                                        return arg.item.active ? ' private-key-active': ' private-key-deactivated';
                                    },
                                    '.fa-key@title':function(arg){
                                        return arg.item.kid+' ('+arg.item.type+')';
                                    },
                                    '.delete-private-key@data-private-key-id': 'private_key.id',
                                    '.private-key-validity-range':function(arg){
                                        return 'from <strong>'+arg.item.valid_from+'</strong> to <strong>'+arg.item.valid_to+'</strong>';
                                    },
                                    '.private-key-fingerprint' : 'private_key.sha_256',
                                    '.private-key-title' : function(arg){
                                        var usage = '<span class="badge private-key-usage pointable" title="Key Usage">'+arg.item.usage+'</span>';
                                        var type  = '<span class="label label-info pointable" title="Key Type">'+arg.item.type+'</span>';
                                        return arg.item.kid+'&nbsp;'+usage+'&nbsp;'+type;
                                    },
                                    '@id':function(arg){
                                        return 'tr_'+arg.item.id;
                                    }
                                }
                            }
                        };

                        var html = template.render(private_keys, directives);
                        $('#body-private-keys').html(html.html());
                        $('.private-keys-empty-message').hide();
                    }
                    else{
                        $('.private-keys-empty-message').show();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    ajaxError(jqXHR, textStatus, errorThrown);
                }
            }
        );
    }

// End of closure.
}( jQuery ));