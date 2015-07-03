@if(count($attributes)>0)
<label>
    <b>The site has also requested some personal information</b>
</label>
<ul class="unstyled list-inline">
    @foreach ($attributes as $attr)
    <li>{{$attr}}&nbsp;<span class="glyphicon glyphicon-info-sign pointable" aria-hidden="true" title=""></span></li>
    @endforeach
</ul>
@endif
@if(!empty($policy_url))
<a href='{{$policy_url}}' title="how the profile data will be used">Policy Url</a>
@endif