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

        <div class="card">
            <div class="card-header">
                <h2>BTC Send a token payment to an address with some optional arguments</h2>
            </div>
            <div class="card-body">
                {!! QrCode::size(500)->BTC('address', 0.0034, [
                    'label' => 'my label',
                    'message' => 'my message',
                    'returnAddress' => 'https://www.returnaddress.com',
                ]) !!}
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Fills in the email address to send to</h2>
            </div>
            <div class="card-body">
                {!! QrCode::email('njokuchimauche@gmail.com') !!}
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Fills in the to address, subject, and body of an e-mail</h2>
            </div>
            <div class="card-body">
                {!! QrCode::email('njokuchimauche@gmail.com', 'This is the subject.', 'This is the message body.') !!}
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>GEO helper generates latitude and longitude that a phone can read and opens the location</h2>
            </div>
            <div class="card-body">
                {!! QrCode::geo(37.822214, -122.481769) !!}
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>This helper generates a QR code that can be scanned and then dials a number</h2>
            </div>
            <div class="card-body">
                {!! QrCode::phoneNumber('+2348165238240') !!}
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Helper to send text message</h2>
            </div>
            <div class="card-body">
                {!! QrCode::SMS('555-555-5555') !!}
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Send SMS with parameters</h2>
            </div>
            <div class="card-body">
                {!! QrCode::SMS('555-555-5555', 'Body of the message') !!}
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Connect to WiFi</h2>
            </div>
            <div class="card-body">
                {!! QrCode::wiFi([
                    'encryption' => 'WPA/WEP',
                    'ssid' => 'SSID of the network',
                    'password' => 'Password of the network',
                    'hidden' => 'Whether the network is a hidden SSID or not.',
                ]) !!}
            </div>
        </div>


    </div>
</body>

</html>
