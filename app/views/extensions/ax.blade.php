@if(count($attributes)>0)
    <label>
        <b>The site has also requested some personal information</b>
    </label>
    <ul class="unstyled list-inline">
    @foreach ($attributes as $attr)
        <li>{{$attr}}&nbsp;<i class="icon-info-sign info"></i></li>
    @endforeach
    </ul>
@endif