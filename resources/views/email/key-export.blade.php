<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Key Export</title>
</head>
<body>
Filtere Seçeneklerin: <br>
Status :{{$filterData['status']}}<br>

@if(!empty( $filterData['games'] ))
    <ol>
        Games
        @foreach($filterData['games'] as $game)
            <li>ID: {{$game->id}} Name: {{$game->name}}</li>
        @endforeach
    </ol>
@endif

@if(!empty( $filterData['suppliers'] ))
    <ol>
        Suppliers
        @foreach($filterData['suppliers'] as $supplier)
            <li>ID: {{$supplier->id}} Name: {{$supplier->name}}</li>
        @endforeach
    </ol>
@endif

@if(!empty( $filterData['customers'] ))
    <ol>
        Customers
        @foreach($filterData['customers'] as $customer)
            <li>ID: {{$customer->id}} Name: {{$customer->name}}</li>
        @endforeach
    </ol>
@endif

<a href="{{$excelUrl}}">Excel Url</a>
</body>
</html>
