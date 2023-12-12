<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>How to Generate QR Code in Laravel 9</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body>

    <div class="container mt-4">

        <div class="card">
            <div class="card-header">
                <h2>Simple QR Code</h2>
            </div>
            <div class="card-body">
                {!! QrCode::size(300)->generate('https://techvblogs.com/blog/generate-qr-code-laravel-9') !!}
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Color QR Code</h2>
            </div>
            <div class="card-body">
                {!! QrCode::size(300)->backgroundColor(255, 90, 0)->generate('https://techvblogs.com/blog/generate-qr-code-laravel-9') !!}
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>BTC Send a token payment to an address</h2>
            </div>
            <div class="card-body">
                {!! QrCode::BTC('bitcoin address', 0.334) !!}
            </div>
        </div>

    </div>
</body>

</html>
