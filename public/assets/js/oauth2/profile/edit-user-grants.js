function updateAccessTokenList(page, page_size){
    //reload access tokens
    $.ajax({
        type: "GET",
        url: TokensUrls.AccessTokenUrls.get +'?offset='+page+'&limit='+page_size,
        dataType: "json",
        timeout:60000,
        success: function (data,textStatus,jqXHR) {
            //load data...

            if(data.items.length === 0){
                $('#table-access-tokens').hide();
                $('#info-access-tokens').show();
            }
            else{
                $('#info-access-tokens').hide();
                $('#table-access-tokens').show();
                var template   = $('<tbody><tr><td class="issued"></td><td class="scope"></td><td class="lifetime"></td><td><a title="Revoke Access Token" class="btn btn-default btn-md active btn-delete revoke-token revoke-access-token" data-hint="access-token">Revoke</a></td></tr></tbody>');
                var directives = {
                    'tr':{
                        'token<-context':{
                            '@id'        :'token.value',
                            'td.issued'  :'token.issued',
                            'td.scope'   :'token.scope',
                            'td.lifetime':'token.lifetime',
                            'a@href':function(arg){
                                var token_value = arg.item.value;
                                var href = TokensUrls.AccessTokenUrls.delete;
                                return href.replace('-1',token_value);
                            },
                            'a@data-value' :'token.value'
                        }
                    }
                };
                var html = template.render(data.items, directives);
                $('#body-access-tokens').html(html.html());
                var pages_html = '';
                for(var i = 0 ; i <  data.pages ; i++){
                    pages_html += "<li><a class='access_token_page' href='#' data-page-nbr='"+(i+1)+"'>"+(i+1)+"</a></li>";
                }
                $('#access_token_paginator').html(pages_html)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            ajaxError(jqXHR, textStatus, errorThrown);
        }
    });
}

function updateRefreshTokenList(page, page_size){
    //reload access tokens
    $.ajax({
        type: "GET",
        url: TokensUrls.RefreshTokenUrl.get+'?offset='+page+'&limit='+page_size,
        dataType: "json",
        timeout:60000,
        success: function (data,textStatus,jqXHR) {
            //load data...

            if(data.items.length===0){
                $('#table-refresh-tokens').hide();
                $('#info-refresh-tokens').show();
            }
            else{
                $('#info-refresh-tokens').hide();
                $('#table-refresh-tokens').show();
                var template   = $('<tbody><tr><td class="issued"></td><td class="scope"></td><td class="lifetime"></td><td><a title="Revoke Refresh Token" class="btn btn-default btn-md active btn-delete revoke-token revoke-refresh-token" data-hint="refresh-token">Revoke</a></td></tr></tbody>');
                var directives = {
                    'tr':{
                        'token<-context':{
                            '@id'        :'token.value',
                            'td.issued'  :'token.issued',
                            'td.scope'   :'token.scope',
                            'td.lifetime':function(arg){
                                var token_lifetime = arg.item.lifetime;
                                return token_lifetime===0?'Not Expire':token_lifetime;
                            },
                            'a@href':function(arg){
                                var token_value = arg.item.value;
                                var href = TokensUrls.RefreshTokenUrl.delete;
                                return href.replace('-1',token_value);
                            },
                            'a@data-value' :'token.value'
                        }
                    }
                };
                var html = template.render(data.items, directives);
                $('#body-refresh-tokens').html(html.html());
                for(var i = 0 ; i <  data.pages ; i++){
                    pages_html += "<li><a class='refresh_token_page' href='#' data-page-nbr='"+(i+1)+"'>"+(i+1)+"</a></li>";
                }
                $('#refresh_token_paginator').html(pages_html)
                updateAccessTokenList();
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            ajaxError(jqXHR, textStatus, errorThrown);
        }
    });
}

jQuery(document).ready(function($){

    var pageSize = 25;

    $('#oauth2-console','#main-menu').addClass('active');

    if($('#table-access-tokens tr').length===1){
        $('#info-access-tokens').show();
        $('#table-access-tokens').hide();
    }
    else{
        $('#info-access-tokens').hide();
        $('#table-access-tokens').show();
    }

    if($('#table-refresh-tokens tr').length===1){
        $('#info-refresh-tokens').show();
        $('#table-refresh-tokens').hide();
    }
    else{
        $('#info-refresh-tokens').hide();
        $('#table-refresh-tokens').show();
    }

    $("body").on("click",".access_token_page", function(event){
        event.preventDefault();
        var page = $(this).data('page-nbr');

        updateAccessTokenList(page, pageSize);

        return false;
    });

    $("body").on("click",".refresh_token_page", function(event){
        event.preventDefault();
        var page = $(this).data('page-nbr');

        updateRefreshTokenList(page, pageSize);

        return false;
    });

    $("body").on('click',".revoke-token",function(event){

        var link        = $(this);
        var value       = link.attr('data-value');
        var hint        = link.attr('data-hint');
        var url         = link.attr('href');
        var table_id    = hint ==='refresh-token'? 'table-refresh-tokens':'table-access-tokens';
        var info_id     = hint ==='refresh-token'? 'info-refresh-tokens':'info-access-tokens';
        var confirm_msg = hint ==='refresh-token'? 'Are you sure?, revoking this refresh token also will become void all related Access Tokens':'Are you sure?';
        if(confirm(confirm_msg)){

            $.ajax(
                {
                    type: "DELETE",
                    url: url,
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                        var row = $('#'+value);
                        row.remove();
                        var row_qty = $('#'+table_id+' tr').length;
                        if(row_qty===1){ //only we have the header ...
                            $('#'+table_id).hide();
                            $('#'+info_id).show();
                        }
                        if(hint=='refresh-token'){
                            updateAccessTokenList();
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