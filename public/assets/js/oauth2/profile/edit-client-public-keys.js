(function( $ ){

    $(document).ready(function($){

        var modal = $('#ModalAddPublicKey');
        modal.modal({show:false});

        $('#form-add-public-key .input-daterange').datepicker({
            startDate: "today",
            todayBtn: "linked",
            clearBtn: true,
            todayHighlight: true,
            orientation: "top right",
            autoclose: true
        });

        // public key form
        var form      = $('#form-add-public-key');

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
                "pem_content"  : {
                    required: true,
                    pem_public_key : true
                },
                "alg" : {required: true}
            }
        });

        $('#usage').change(function(){

            var usage = $(this).val();

            var alg_select = $('#alg');

            alg_select.empty();

            var result = [];

            if(usage === 'sig')
            {
                result = oauth2_supported_algorithms.sig_algorihtms.rsa;
            }
            else
            {
                result = oauth2_supported_algorithms.key_management_algorihtms;
            }

            $.each(result, function(index, item) {
                var key = item === 'none' ? '' : item;
                alg_select.append($("<option />").val(key).text(item));
            });
        });

        $('#usage').trigger('change');

        $("body").on('click',".add-public-key",function(event){
            modal.modal('show');
            validator.resetForm();
            $('#active').prop('checked', true);
            event.preventDefault();
            return false;
        });

        $("body").on('click',".delete-public-key",function(event){
            if(window.confirm('are you sure?')){
                //delete key
                var public_key_id = $(this).attr('data-public-key-id');

                $.ajax(
                    {
                        type: "DELETE",
                        url: dataClientUrls.delete_public_key.replace('@public_key_id', public_key_id),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        timeout: 60000,
                        success: function (data, textStatus, jqXHR) {
                            $('#tr_'+public_key_id).fadeOut(300, function() {
                                $(this).remove();
                                if($('#body-public-keys').children('tr').length)
                                    $('.public-keys-empty-message').hide();
                                else
                                    $('.public-keys-empty-message').show();
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

        $("body").on('click',".save-public-key",function(event){

            if(form.valid()) {

                var public_key_data = form.serializeForm();
                public_key_data.type = 'RSA';
                $.ajax(
                    {
                        type: "POST",
                        url: dataClientUrls.add_public_key,
                        data: JSON.stringify(public_key_data),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        timeout: 60000,
                        success: function (data, textStatus, jqXHR) {
                            modal.modal('hide');
                            form.cleanForm();
                            $('.public-keys-empty-message').hide();
                            loadPublicKeys();
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

        $("body").on('click',".public-key-status",function(event){

            var status_badge       = $(this);
            var public_key_id      = status_badge.attr('data-public-key-id');
            var public_key_data    = { id : public_key_id };
            public_key_data.active = status_badge.hasClass('public-key-active') ? false : true;

            $.ajax(
                {
                    type: "PUT",
                    url: dataClientUrls.update_public_key.replace('@public_key_id', public_key_id),
                    contentType: "application/json; charset=utf-8",
                    data: JSON.stringify(public_key_data),
                    dataType: "json",
                    timeout: 60000,
                    success: function (data, textStatus, jqXHR) {
                        loadPublicKeys();
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

    function loadPublicKeys(){

        $.ajax({
                type: "GET",
                url: dataClientUrls.get_public_keys+'?offset=1&limit=4294967296',
                dataType: "json",
                timeout:60000,
                success: function (data,textStatus,jqXHR) {
                    //load data...
                    var public_keys = data.page;

                    if(public_keys.length > 0){

                        var template = $('<tbody>' +
                            '<tr>'+
                            '<td width="7%">'+
                            '<div class="row">'+
                            '<div class="col-md-6">'+
                            '<span class="badge public-key-status">&nbsp</span>'+
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
                            '<strong class="public-key-title"></strong>'+
                            '</div>'+
                            '</div>'+
                            '<div class="row">'+
                            '<div class="col-md-12">'+
                            '<code class="public-key-fingerprint"></code>'+
                            '</div>'+
                            '</div>'+
                            '<div class="row">'+
                            '<div class="col-md-12">'+
                            '<span class="public-key-validity-range"></span>'+
                            '</div>'+
                            '</div>'+
                            '</div>'+
                            '</div>'+
                            '</td>'+
                            '<td><a class="btn btn-default btn-sm active delete-public-key btn-delete" href="#">Delete</a></td>'+
                            '</tr>'+
                            '</tbody>');

                        var directives = {
                            'tr':{
                                'public_key<-context':{
                                    '.public-key-status@title':function(arg){
                                        return arg.item.active ? 'active': 'deactivated';
                                    },
                                    '.public-key-status@data-public-key-id':  'public_key.id',
                                    '.public-key-status@class+':function(arg){
                                        return arg.item.active ? ' public-key-active': ' public-key-deactivated';
                                    },
                                    '.fa-key@title':function(arg){
                                        return arg.item.kid+' ('+arg.item.type+')';
                                    },
                                    '.delete-public-key@data-public-key-id': 'public_key.id',
                                    '.public-key-validity-range':function(arg){
                                        return 'valid from <strong>'+arg.item.valid_from+'</strong> to <strong>'+arg.item.valid_to+'</strong>';
                                    },
                                    //'td.public-key-usage'      : 'public_key.usage',
                                    '.public-key-fingerprint' : 'public_key.sha_256',
                                    '.public-key-title' : function(arg){
                                        var usage = '<span class="badge public-key-usage pointable" title="Key Usage">'+arg.item.usage+'</span>';
                                        var type  = '<span class="label label-info pointable" title="Key Type">'+arg.item.type+'</span>';
                                        var alg   = '<span title="alg: identifies the algorithm intended for use with the key" class="label label-primary pointable">'+arg.item.alg+'</span>';
                                        return arg.item.kid+'&nbsp;'+usage+'&nbsp;'+type+'&nbsp;'+alg;
                                    },
                                    '@id':function(arg){
                                        return 'tr_'+arg.item.id;
                                    }
                                }
                            }
                        };

                        var html = template.render(public_keys, directives);
                        $('#body-public-keys').html(html.html());
                        $('.public-keys-empty-message').hide();
                    }
                    else{
                        $('.public-keys-empty-message').show();
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