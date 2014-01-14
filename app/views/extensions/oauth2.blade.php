@if(count($requested_scopes)>0)
<div class="container">
    <div class="row-fluid">
        <div class="span6 offset3">
            <div class="row-fluid">
                <div class="span2">
                    <img src="{{$app_logo}}" border="0"/>
                </div>
                <div class="span10">
                    <h2>{{$app_name}}&nbsp;<i data-content="Developer Email: <a href='#'>{{$dev_info_email}}</a>.<br> Clicking 'Accept' will redirect you to: <a href='#'>{{$redirect_to}}</a>" class="icon-info-sign info" title="Developer Info"></i></h2>
                </div>
            </div>
            <legend>This app would like to:</legend>
            <ul class="unstyled list-inline">
                @foreach ($requested_scopes as $scope)
                <li> {{$scope->short_description}}&nbsp;<i class="icon-info-sign info" data-content="{{$scope->description}}" title="Scope Info"></i></li>
                @endforeach
            </ul>
            <p class="privacy-policy">
                ** <b>{{$app_name}}</b> Application and <b>Openstack</b> will use this information in accordance with their respective terms of service and privacy policies.
            </p>

        </div>
    </div>
</div>
@endif