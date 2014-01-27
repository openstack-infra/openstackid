function ajaxError(jqXHR, textStatus, errorThrown){
    var HTTP_status = jqXHR.status;
    if(HTTP_status!=200){
        response = $.parseJSON(jqXHR.responseText);
        if(response.error==='validation'){
            var msg = '';
            for(var property in response.messages) {
                msg +='* '+response.messages[property]+'\n';
            }
            alert(msg);
        }
        else
            alert(response.error);
    }
    else{
        alert('server error');
    }
}
