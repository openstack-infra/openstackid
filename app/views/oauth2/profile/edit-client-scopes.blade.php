<div class="row-fluid">
    <div class="span12">
        <ul class="unstyled list-inline">
            <?php $last_api = ''; ?>
            @foreach ($scopes as $scope)
            {{-- get api data --}}
            <?php $current_api      = $scope->getApiName(); ?>
            <?php $current_api_logo = $scope->getApiLogo(); ?>
            {{-- if we have set an api --}}
            @if($last_api!=$current_api && !empty($current_api))
                {{-- check end of former api --}}
                @if(!empty($last_api))
                    </li></ul>
                @endif
                <?php $last_api = $current_api;?>
                {{-- draw api header --}}
                <li>
                @if(!empty($current_api_logo))
                <img width="24" height="24" src="{{$current_api_logo}}" alt="api logo"/>
                @endif
                <span>{{trim($current_api)}}</span>&nbsp;<i class="icon-info-sign accordion-toggle" title="{{$scope->getApiDescription()}}"></i>
                <ul class="unstyled list-inline" style="margin-left: 2em">
            @endif
            {{-- scope header --}}
            <li>
                <label class="checkbox">
                    <input type="checkbox" class="scope-checkbox" id="scope[]"
                    @if ( in_array($scope->id,$selected_scopes))
                    checked
                    @endif
                    value="{{$scope->id}}"/><span>{{trim($scope->name)}}</span>&nbsp;<i class="icon-info-sign accordion-toggle" title="{{$scope->description}}"></i>
                </label>
            </li>
            {{-- end scope header --}}
            @endforeach
            {{-- check end of former api --}}
            @if(!empty($last_api))
                </li></ul>
            @endif
        </ul>
    </div>
</div>
@section('scripts')
@parent
<script type="application/javascript">
    $(document).ready(function() {
        $("body").on('click',".scope-checkbox",function(event){
            var scope = {};
            scope.scope_id = $(this).attr('value');
            scope.checked  = $(this).is(':checked');
            $.ajax(
                {
                    type: "POST",
                    url: '{{URL::action("ClientApiController@addAllowedScope",array("id"=>$client->id))}}',
                    data: JSON.stringify(scope),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        ajaxError(jqXHR, textStatus, errorThrown);
                    }
                }
            );
        });
    });
</script>
@stop