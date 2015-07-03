@extends('layout')

@section('title')
<title>Welcome to openstackId - Server Admin - Banned Ips</title>
@stop

@section('content')
@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
<legend>Banned Ips</legend>
<div class="row">
    <div class="col-md-12">

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
                    {{ HTML::link(URL::action("ApiBannedIPController@delete",array("id"=>$ip->id)),'Revoke',array('data-ip-id'=>$ip->id,'class'=>'btn btn-default btn-md active revoke-ip','title'=>'Revoke given banned ip address')) }}
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
{{ HTML::script('assets/js/admin/banned-ips.js') }}
@append
