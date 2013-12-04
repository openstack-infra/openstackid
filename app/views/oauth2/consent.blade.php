@extends('layout')
@section('title')
<title>Welcome to openstackId - consent </title>
@stop
@section('content')
<div class="container">
    <h4>OpenstackId - OAuth2 verification</h4>
    {{ Form::open(array('url' => '/accounts/user/consent','id'=>'authorization_form', 'method' => 'post',  "autocomplete" => "off")) }}
    <fieldset>
        <div>
            <label class="radio">
                {{ Form::radio('trust[]', 'AllowOnce','true',array('id'=>'allow_once','class'=>'input-block-level')) }}
                Allow Once
            </label>
            <label class="radio">
                {{ Form::radio('trust[]', 'AllowForever','',array('id'=>'allow_forever','class'=>'input-block-level')) }}
                Allow Forever
            </label>
            <label class="radio">
                {{ Form::radio('trust[]', 'DenyOnce','',array('id'=>'deny_once','class'=>'input-block-level')) }}
                Deny Once
            </label>
            <label class="radio">
                {{ Form::radio('trust[]', 'DenyForever','',array('id'=>'deny_forever','class'=>'input-block-level')) }}
                Deny Forever
            </label>
        </div>
        {{ Form::submit('Ok',array("id"=>"send_authorization",'class'=>'btn')) }}
        {{ Form::button('Cancel',array('id'=>'cancel_authorization','class'=>'btn cancel_authorization')) }}
    </fieldset>
    {{ Form::close() }}
</div>
@stop

@section('scripts')
<script type="application/javascript">
    $(document).ready(function() {
        $("body").on('click',"#cancel_authorization",function(event){
            $form = $('#authorization_form');
            $("#deny_once").prop("checked", true)
            $form.submit();
            event.preventDefault();
            return false;
        });
    });
</script>
@stop