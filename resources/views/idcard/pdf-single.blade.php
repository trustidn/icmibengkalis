<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>ID Card</title>
    @include('idcard.style')
    <style>
        @page { margin: 0; }
        body { margin: 0; }
    </style>
</head>
<body>
    @include('idcard.card', $card)
</body>
</html>
