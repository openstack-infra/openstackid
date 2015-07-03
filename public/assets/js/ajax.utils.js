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

function displayErrorMessage(title,message){
    var $alert = $('<div id="modal-message" class="modal hide fade error-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button><h4 id="myModalLabel" class="alert-heading">'+title+'</h4></div><div class="modal-body"></div><div class="modal-footer"><button class="btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button></div></div>');
    $('.modal-body',$alert).html(message || "");
    $('body').append($alert);
    $alert.modal({show:true});
}

function displaySuccessMessage(message,$element){
    var element_id = $element.attr('id');
    var $alert = $('.alert-success').filter(function() {
        return $(this).attr("for") === element_id;
    });
    if($alert.length){
        $('.msg',$alert).html(message || "");
    }
    else{
        var $alert = $('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button><span class="msg"></span></div>').attr('for',element_id);
        $('.msg',$alert).html(message || "");
        $alert.insertBefore($element);
    }
}

function displayAlert(msg,after){
    $('.alert-error').remove();
    var alert = $('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>'+msg+'</div>');
    alert.insertAfter(after);
}