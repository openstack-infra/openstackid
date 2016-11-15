@if(count($requested_scopes)>0)
<label>
    <b>The site has also requested some permissions for following OAuth2 application</b>
</label>
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-2">
                    <img src="{!!$app_logo!!}" border="0"/>
                </div>
                <div class="col-md-10">
                    <h2>{!!$app_name!!}&nbsp;<span class="glyphicon glyphicon-info-sign pointable" aria-hidden="true" data-content="Developer Email: <a href='mailto:{!!$dev_info_email!!}'>{!!$dev_info_email!!}</a>.<br> Clicking 'Accept' will redirect you to: <a href='{!!$website!!}' target='_blank'>{!!$website!!}</a>" title="Developer Info"></span></h2>
                </div>
            </div>
            <legend>This app would like to:</legend>
            <ul class="unstyled list-inline">
                @foreach ($requested_scopes as $scope)
                <li> {!!$scope->short_description!!}&nbsp;<span class="glyphicon glyphicon-info-sign pointable" aria-hidden="true" data-content="{!!$scope->description!!}" title="Scope Info"></span></li>
                @endforeach
            </ul>
            <p class="privacy-policy">
                ** <b>{!!$app_name!!}</b> Application and <b>Openstack</b> will use this information in accordance with their respective terms of service and privacy policies.
            </p>
        </div>
    </div>
</div>
@endif

@section('scripts')
<script type="application/javascript">
    $(document).ready(function() {

        var hideAllPopovers = function() {
            $('.icon-info-sign').each(function() {
                $(this).popover('hide');
            });
        };

        $('.icon-info-sign').popover({html:true});

        $(':not(#anything)').on('click', function (e) {
            $('.icon-info-sign').each(function () {
                //the 'is' for buttons that trigger popups
                //the 'has' for icons and other elements within a button that triggers a popup
                if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                    $(this).popover('hide');
                    return;
                }
            });
        });
    });
</script>
@append