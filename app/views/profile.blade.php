@extends('layout')
@section('content')
<h1>OpenstackId Idp</h1>
<div class="container">
    Hello, {{{ $username }}}.
    <a href="{{ URL::action("UserController@logout") }}"">logout</a>
    <div>Your OPENID: {{$openid_url}}</div>
    @if (count($sites)>0)
    <div id="trusted_sites">
        <h3>Trusted Sites</h3>
        <ul>
        @foreach ($sites as $site)
            <li><div><span>Realm {{ $site->getRealm() }} - Policy {{ $site->getAuthorizationPolicy() }}</span>&nbsp;{{ HTML::link(URL::action("UserController@get_deleteTrustedSite",array("id"=>$site->id)),'Delete',array('class'=>'btn del-realm')) }}</div></li>
        @endforeach
        </ul>
    </div>
    @endif
</div>
@section('scripts')
<script type="application/javascript">
    $(document).ready(function() {

        /*$("#trusted_sites").on('click',".del-realm",function(event){
           var url = $(this).attr("href") ;
            $.ajax({
                url: url,
                type: "GET",
                dataType : "json",
                success: function( json ) {
                },
                error: function( xhr, status ) {
                    alert( "Sorry, there was a problem!" );
                }
            });
           event.preventDefault();
           return false;
        });*/
    });
</script>
@stop