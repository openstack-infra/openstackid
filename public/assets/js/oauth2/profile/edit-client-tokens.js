var pageSizeTokens           = 25;
var refreshTokenCurrentPage  = 1;
var accessTokenCurrentPage   = 1;

function updateAccessTokenList(page, page_size){
    //reload access tokens
    accessTokenCurrentPage = page;
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
                        var active = ((i+1) == accessTokenCurrentPage) ? true : false;
                        pages_html += "<li "+(active ? "class='active'":"" )+"><a class='access_token_page' href='#' data-page-nbr='"+(i+1)+"'>"+(i+1)+"</a></li>";
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
    refreshTokenCurrentPage = page;
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
                    var pages_html = '';
                    for(var i = 0 ; i <  data.pages ; i++){
                        var active = ((i+1) == refreshTokenCurrentPage) ? true : false;
                        pages_html += "<li "+(active ? "class='active'":"" )+"><a class='refresh_token_page' href='#' data-page-nbr='"+(i+1)+"'>"+(i+1)+"</a></li>";
                    }
                    $('#refresh_token_paginator').html(pages_html);
                    updateAccessTokenList(1, pageSizeTokens);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                ajaxError(jqXHR, textStatus, errorThrown);
            }
    });
}

jQuery(document).ready(function($){



    if($('#table-access-tokens tr').length == 1){
        $('#info-access-tokens').show();
        $('#table-access-tokens').hide();
    }
    else{
        $('#info-access-tokens').hide();
        $('#table-access-tokens').show();
    }

    if($('#table-refresh-tokens tr').length == 1){
        $('#info-refresh-tokens').show();
        $('#table-refresh-tokens').hide();
    }
    else{
        $('#info-refresh-tokens').hide();
        $('#table-refresh-tokens').show();
    }

    $("body").on('click','.refresh-access-tokens',function(event){
        updateAccessTokenList(1, pageSizeTokens);
        event.preventDefault();
        return false;
    });

    $("body").on('click','.refresh-refresh-tokens',function(event){
        updateRefreshTokenList(1, pageSizeTokens);
        event.preventDefault();
        return false;
    });

    $("body").on("click",".access_token_page", function(event){
        event.preventDefault();
        accessTokenCurrentPage = $(this).data('page-nbr');

        updateAccessTokenList(accessTokenCurrentPage, pageSizeTokens);

        return false;
    });

    $("body").on("click",".refresh_token_page", function(event){
        event.preventDefault();
        refreshTokenCurrentPage= $(this).data('page-nbr');

        updateRefreshTokenList(refreshTokenCurrentPage, pageSizeTokens);

        return false;
    });

    $("body").on('click',".revoke-token",function(event){

        var link        = $(this);
        var value       = link.data('value');
        var hint        = link.data('hint');
        var url         = link.attr('href');
        var table_id    = hint ==='refresh-token'? 'table-refresh-tokens':'table-access-tokens';
        var info_id     = hint ==='refresh-token'? 'info-refresh-tokens':'info-access-tokens';
        var confirm_msg = hint ==='refresh-token'? 'Revoking this refresh token also will become void all related Access Tokens.':'Revoke Access Token?';

        swal({
                title: "Are you sure?",
                text: confirm_msg,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, revoke it!",
                closeOnConfirm: true
            }).then(
            function(result){
                if(!result) return;
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
                                if(hint=='refresh-token' && refreshTokenCurrentPage > 1) {
                                    refreshTokenCurrentPage -= 1;
                                    updateRefreshTokenList(refreshTokenCurrentPage, pageSizeTokens);
                                }
                                if(hint=='access-token' && accessTokenCurrentPage > 1) {
                                    accessTokenCurrentPage -= 1;
                                    updateAccessTokenList(accessTokenCurrentPage, pageSizeTokens);
                                }
                                else{
                                    $('#'+table_id).hide();
                                    $('#'+info_id).show();
                                }
                            }
                            if(hint=='refresh-token'){
                                updateAccessTokenList(1, pageSizeTokens);
                            }
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
});