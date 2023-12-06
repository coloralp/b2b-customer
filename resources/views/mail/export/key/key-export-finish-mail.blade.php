<x-mail::message>
Key Export Your Filters :

    @foreach($result as $index=>$item )

        <p>{{ $index }} : {{ $item }}</p> <br>

    @endforeach
</x-mail::message>
