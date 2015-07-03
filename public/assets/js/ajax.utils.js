function ajaxError(jqXHR, textStatus, errorThrown){
    var HTTP_status = jqXHR.status;
    if(HTTP_status!=200){
        response = $.parseJSON(jqXHR.responseText);
        if(response.error==='validation'){
            var msg = '';
            for(var property in response.messages) {
                msg +='* '+response.messages[property]+'\n';
            }
            displayErrorMessage('You got an error!',msg);
        }
        else
            displayErrorMessage('You got an error!',response.error);
    }
    else{
        displayErrorMessage('You got an error!','server error');
    }
}

function displayErrorMessage(title, message){
    $('#modal_error').remove();
    var modal = $('<div id="modal_error" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">' +
    '<div class="modal-dialog" role="document">' +
    '<div class="modal-content">' +
    '<div class="modal-header">'+
    '<button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
    '<span aria-hidden="true">&times;</span></button>'+
    '<h4 class="modal-title" id="myModalLabel">'+title+'</h4>'+
    '</div>'+
    '<div class="modal-body">'+
    '<p>'+message+'</p>'+
    '</div>'+
    '<div class="modal-footer">'+
    '<button type="button" class="btn btn-danger" data-dismiss="modal" data-backdrop="false">Close</button>'+
    '</div>'+
    '</div><!-- /.modal-content -->'+
    '</div><!-- /.modal-dialog -->'+
    '</div><!-- /.modal -->');
    $('body').append(modal);
    modal.modal({show:true});

    modal.on('hidden.bs.modal', function (e) {
        $('.modal-backdrop').remove();
    })
}

function displaySuccessMessage(message, $element){
    var element_id = $element.attr('id');
    var $alert = $('.alert-success').filter(function() {
        return $(this).attr("for") === element_id;
    });
    if($alert.length){
        $('.msg',$alert).html(message || "");
    }
    else{
        var $alert = $('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span class="msg"></span></div>').attr('for',element_id);
        $('.msg',$alert).html(message || "");
        $alert.insertBefore($element);
    }
}

function displayAlert(msg,after){
    $('.alert-danger').remove();
    var alert = $('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+msg+'</div>');
    alert.insertAfter(after);
}