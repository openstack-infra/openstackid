@extends('layout')

@section('title')
<title>Welcome to openstackId - Server Admin - Banned Ips</title>
@stop

@section('content')
@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
<legend>Banned Ips</legend>
<div class="row-fluid">
    <div class="span12">

        <table id="ips-table" class="table table-hover table-condensed">
            <thead>
            <tr>
                <th>IP Address</th>
                <th>Date</th>
                <th>Hits</th>
                <th>Cause</th>
                <th>User</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody id="body-ips">
            @foreach($ips as $ip)
            <tr id="{{$ip->id}}">
                <td>{{$ip->ip}}</td>
                <td>{{$ip->created_at}}</td>
                <td>{{$ip->hits}}</td>
                <td>{{$ip->exception_type}}</td>
                <td>
                    @if(!is_null($ip->user()->first()))
                    {{ $ip->user()->first()->getEmail() }}
                    @else
                    N\A
                    @endif
                </td>
                <td>
                    {{ HTML::link(URL::action("ApiBannedIPController@delete",array("id"=>$ip->id)),'Revoke',array('data-ip-id'=>$ip->id,'class'=>'btn revoke-ip','title'=>'Revoke given banned ip address')) }}
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        <span id="ips-info" class="label label-info">** There are not any Banned IPs.</span>
    </div>
</div>
@stop

@section('scripts')
<script type="application/javascript">
    $(document).ready(function() {

        $('#server-admin','#main-menu').addClass('active');

        if($('#ips-table tr').length===1){
            $('#ips-info').show();
            $('#ips-table').hide();
        }
        else{
            $('#ips-info').hide();
            $('#ips-table').show();
        }

        $("body").on('click',".revoke-ip",function(event){
            if(confirm("Are you sure that you want to revoke this banned ip?")){
                var url = $(this).attr('href');
                var ip_id = $(this).attr('data-ip-id');
                $.ajax(
                    {
                        type: "DELETE",
                        url: url,
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            //load data...
                            $('#'+ip_id,'#body-ips').remove();

                            if($('#ips-table tr').length===1){
                                $('#ips-info').show();
                                $('#ips-table').hide();
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
