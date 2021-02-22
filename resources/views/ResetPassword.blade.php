<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Reset Password</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Styles -->
    <style>
        a {
            font-size: 18px;
        }
    </style>
</head>

<body>
    <h1>Confirm your password reset - QUBU</h1>
    <p>Hi {{$name ?? ''}}.</p>
    <p>A request has been made to reset your password.</p>
    <div>
        <span>Please click</span>
        <a href="{!! $token ?? '' !!}">here</a>
        <span> to be taken to a page where you'll be able to set a new password.</span>
    </div>

</body>

</html>