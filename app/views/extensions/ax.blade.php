@if(count($attributes)>0)
    <ul>
    @foreach ($attributes as $attr)
        <li>{{$attr}}</li>
    @endforeach
    </ul>
@endif