<div class="row-fluid">
    <div class="span12">
        <ul class="unstyled list-inline"><li>
                <?php $last_api = ''; ?>
                @foreach ($scopes as $scope)
                <?php $current_api = $scope->getApiName(); ?>
                @if($last_api!=$current_api)
                @if($last_api!='')
        </ul><!--scopes-->
        </li><li>
            @endif
            <?php $last_api = $current_api;?>
            {{ $current_api }}&nbsp;<i class="icon-info-sign accordion-toggle" title="{{$scope->getApiDescription()}}"></i>
            <ul class="unstyled list-inline">

                @endif
                <li>
                    <label class="checkbox">
                        <input type="checkbox" class="scope-checkbox" id="scope[]"
                        @if ( in_array($scope->id,$selected_scopes))
                        checked
                        @endif
                        value="{{$scope->id}}"/> {{$scope->name}}&nbsp;<i class="icon-info-sign accordion-toggle" title="{{$scope->description}}"></i>
                    </label>
                </li>
                @endforeach
            </ul><!--scopes-->
        </li></ul>
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
                    url: '{{URL::action("UserController@postAddAllowedScope",array("id"=>$client->id))}}',
                    data: JSON.stringify(scope),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert( "Request failed: " + textStatus );
                    }
                }
            );
        });
    });
</script>
@stop