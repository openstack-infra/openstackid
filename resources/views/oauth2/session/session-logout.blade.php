@extends('layout')

@section('title')
    <title>Welcome to OpenStackId - OpenStack ID Logout</title>
@stop

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-sm-6">
                <p>Would you like to logout from following realms ? </p>
                <p>
                    <ul>
                    @foreach ($clients as $client)
                        <li><b>{!!$client->website!!}</b></li>
                    @endforeach
                    </ul>
                 </p>
                {!! Form::open(array('url' => URL::action('OAuth2\OAuth2ProviderController@endSession'), 'method' => 'post',  "autocomplete" => "off")) !!}
                    <fieldset>
                        <input  type="hidden" name="oidc_endsession_consent" id="oidc_endsession_consent" value="1"/>
                        <input  type="hidden" name="id_token_hint" id="id_token_hint" value="{!!$id_token_hint!!}"/>
                        <input  type="hidden" name="post_logout_redirect_uri" id="post_logout_redirect_uri" value="{!!$post_logout_redirect_uri!!}"/>
                        <input  type="hidden" name="state" id="state" value="{!!$state!!}"/>
                        <div class="form-group">
                            {!! Form::submit('Yes ',array('id'=>'login','class'=>'btn active btn-primary')) !!}
                            <a class="btn btn-danger active" href="{!! URL::action('OAuth2\OAuth2ProviderController@cancelLogout') !!}">No</a>
                        </div>
                    </fieldset>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop
@section('scripts')
    {!! HTML::script('bower_assets/crypto-js/crypto-js.js')!!}
    {!! HTML::script('bower_assets/jquery-cookie/jquery.cookie.js')!!}
@append