@extends('layout')

@section('title')
<title>Welcome to openstackId - Edit Registered Application</title>
@stop

@section('content')
<a href="{{ URL::previous() }}">Go Back</a>

<legend>{{ $client->app_name }}</legend>

@if($errors->any())
<div class="errors">
    <ul>
        @foreach($errors->all() as $error)
        <div class="alert alert-error">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ $error }}
        </div>
        @endforeach
    </ul>
</div>
@endif
<div id="accordion">
    <h3><i class="icon-info-sign accordion-toggle" title="OAuth2 Client ID and Client Secret"></i>&nbsp;OAuth 2.0 Client ID</h3>
    @include('oauth2.profile.edit-client-data',array('access_tokens' => $access_tokens, 'refresh_tokens' => $refresh_tokens,'client'=>$client,'allowed_uris'=>$allowed_uris))
    <h3><i class="icon-info-sign accordion-toggle" title="Authorized Client Redirection Uris"></i>&nbsp;Authorized Redirection Uris</h3>
    @include('oauth2.profile.edit-client-redirect-uris',array('access_tokens' => $access_tokens, 'refresh_tokens' => $refresh_tokens,'client'=>$client,'allowed_uris'=>$allowed_uris))
    <h3><i class="icon-info-sign accordion-toggle" title="Application Allowed Scopes"></i>&nbsp;Application Allowed Scopes</h3>
    @include('oauth2.profile.edit-client-scopes',array('access_tokens' => $access_tokens, 'refresh_tokens' => $refresh_tokens,'client'=>$client) )
    <h3><i class="icon-info-sign accordion-toggle" title="Application Grants"></i>&nbsp;Application Grants</h3>
    @include('oauth2.profile.edit-client-tokens',array('access_tokens' => $access_tokens, 'refresh_tokens' => $refresh_tokens,'client'=>$client) )

</div>
@stop
@section('scripts')
<script type="application/javascript">

    function displayAlert(msg,after){
        $('.alert-error').remove();
        var alert = $('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>'+msg+'</div>');
        alert.insertAfter(after);
    }

    $(document).ready(function() {
        $( "#accordion" ).accordion({
            collapsible: true,
            heightStyle: "content"
        });
    });
</script>
@stop