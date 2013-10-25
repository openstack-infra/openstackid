@extends('layout')
@section('content')
<div class="container">
    {{ Form::open(array('url' => '/accounts/user/consent','id'=>'authorization_form', 'method' => 'post',  "autocomplete" => "off")) }}
    <label>
        This Site {{ $realm }} is requesting permissions
    </label>

    <div>
        {{ Form::label("allow_forever","Allow Forever")}}
        {{ Form::radio('trust[]', 'AllowForever','true',array('id'=>'allow_forever')) }}
        {{ Form::label("allow_once","Allow Once")}}
        {{ Form::radio('trust[]', 'AllowOnce','',array('id'=>'allow_once')) }}
        {{ Form::label("deny_once","Deny Once")}}
        {{ Form::radio('trust[]', 'DenyOnce','',array('id'=>'deny_once')) }}
        {{ Form::label("deny_forever","Deny Forever")}}
        {{ Form::radio('trust[]', 'DenyForever','',array('id'=>'deny_forever')) }}
    </div>
    {{ Form::submit('Ok',array("id"=>"send_authorization",'class'=>'btn')) }}
    {{ Form::button('Cancel',array('id'=>'cancel_authorization','class'=>'btn cancel_authorization')) }}
    {{ Form::close() }}
    @foreach ($views as $view)
    {{ $view}}
    @endforeach
</div>
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