@if(count($attributes)>0)
    <ul>
    @foreach ($attributes as $attr)
        <li>{{$attr}}&nbsp;<i class="icon-info-sign"></i></li>
    @endforeach
    </ul>
@endif