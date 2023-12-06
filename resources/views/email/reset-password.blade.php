<!DOCTYPE html>
<html>
<head>
    <title>Şifre Sıfırlama</title>
</head>
<body>
<h1>Merhaba {{ $user->name }},</h1>
<p>Şifrenizi sıfırlamak istediğinizi belirttiniz. Aşağıdaki bağlantıya tıklayarak şifrenizi sıfırlayabilirsiniz.</p>
{{--<a href="{{ url('password/reset/', $user->id,'/',['token' => $token]) }}">Şifreyi Sıfırla</a>--}}
{{--<a href="{{  'https://b2b.cdkeyci.com/password/reset/'. $token  }}">Şifreyi Sıfırla</a>--}}
<a href="{{ "https://b2b.cdkeyci.com/password/reset/".$token }}">Şifreyi Sıfırla</a>
</body>
</html>
