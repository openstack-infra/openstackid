@if(count($attributes)>0)
    <label>
        The site has also requested some personal information
    </label>
    <ul class="unstyled list-inline">
    @foreach ($attributes as $attr)
        <li>{{$attr}}&nbsp;<i class="icon-info-sign"></i></li>
    @endforeach
    </ul>
@endif