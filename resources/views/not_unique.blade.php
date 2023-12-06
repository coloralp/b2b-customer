<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css"
          integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

    <title>Hello, world!</title>
</head>
<body>


<table class="table">
    <thead>
    <tr>
        <th scope="col">Key Code</th>
        <th scope="col">Tekrarlanan</th>
    </tr>
    </thead>
    <tbody>
    @foreach($uniqueKeys as $uniqueKey)
        <tr>
            <th scope="row">{{$uniqueKey->key}}</th>
            <td>{{$uniqueKey->sayi}}</td>
        </tr>
    @endforeach
    </tbody>
</table>


<h3>Detay Bilgi</h3>

Bunların Hepsi satıldı mı ? {{$result['hepsi_satildi_mi']}} <br>


<table class="table">
    <thead>
    <tr>
        <th scope="col">Key Code</th>
        <th scope="col">Game</th>
        <th scope="col">Status</th>
        <th scope="col">Order Code</th>
        <th scope="col">Kime</th>
    </tr>
    </thead>
    <tbody>
    @foreach($result as $uniqueKey)
        @if(is_array($uniqueKey))
            <tr>
                <th scope="row">{{$uniqueKey['key_code']}}</th>
                <td>{{$uniqueKey['game']}}</td>
                <td>{{ $uniqueKey['status'] == 4 ? 'SATILD'  : 'SATILMAMIŞ =>'."DURUMU {$uniqueKey['status']}"}}</td>
                <td>{{ $uniqueKey['order_code'] }}</td>
                <td>{{ $uniqueKey['kime'] }}</td>
            </tr>
        @endif
    @endforeach
    </tbody>
</table>


<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
        integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js"
        integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
        crossorigin="anonymous"></script>
</body>
</html>
