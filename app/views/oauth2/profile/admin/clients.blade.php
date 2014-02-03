@extends('layout')

@section('title')
<title>Welcome to openstackId - Server Admin - OAUTH2 - Clients</title>
@stop

@section('content')
@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
<legend>Locked OAUTH2 Clients</legend>
<div class="row-fluid">
    <div class="span12">

        <table id='clients-table' class="table table-hover table-condensed">
            <thead>
            <tr>
                <th>Client</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody id="body-locked-clients">
              @foreach($clients as $client)
              <tr id="{{$client->id}}">
                  <td>
                      <div style="min-width: 500px">
                      {{ $client->getApplicationName() }}
                      </div>
                  </td>
                  <td>
                      {{ HTML::link(URL::action("ClientApiController@unlock",array("id"=>$client->id)),'Unlock',array('class'=>'btn unlock-client','data-client-id'=>$client->id,'title'=>'Unlocks given client')) }}
                  </td>
              </tr>
              @endforeach
            </tbody>
        </table>

        <span id="clients-info" class="label label-info">** There are not any locked OAUTH2 Client.</span>

    </div>
</div>
@stop

@section('scripts')
<script type="application/javascript">
    $(document).ready(function() {
        $('#server-admin','#main-menu').addClass('active');

        if($('#clients-table tr').length===1){
            $('#clients-info').show();
            $('#clients-table').hide();
        }
        else{
            $('#clients-info').hide();
            $('#clients-table').show();
        }

        $("body").on('click',".unlock-client",function(event){
            if(confirm("Are you sure that you want to unlock this OAUTH2 Client?")){
                var url = $(this).attr('href');
                var client_id = $(this).attr('data-client-id');
                $.ajax(
                    {
                        type: "DELETE",
                        url: url,
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            //load data...
                            $('#'+client_id,'#body-locked-clients').remove();

                            if($('#clients-table tr').length===1){
                                $('#clients-info').show();
                                $('#clients-table').hide();
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
</script>
@stop