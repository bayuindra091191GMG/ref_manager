<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simi - Voucher Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 600px;
            margin: auto;
        }
        .header {
            text-align: center;
            padding: 10px 0;
        }
        .content {
            font-size: 16px;
            line-height: 1.5;
            margin: 20px 0;
        }
        .otp-code {
            font-size: 16px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            padding: 12px 25px;
            cursor: pointer;
            text-decoration: none;
            background-color: #FB5E33; /* Corporate color */
        }
        .voucher-name {
            font-size: 18px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #888;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="{{ asset('images/logo_karada_orange_email.jpg') }}" alt="karada" width="200" />
    </div>
    <div class="content">
        <h2>Hello,</h2>
        <p>Enjoy the promo!</p>
        <br />
        @foreach($urls as $index => $url)
            <a href="{{ $url }}" class="otp-code" style="color: #ffffff; margin-right: 10px;">Voucher {{ $index + 1 }}</a>
            @if(($index + 1) % 4 == 0)
                <br />
                <br />
            @endif
        @endforeach
    </div>
    <div class="footer">
        <p>&copy; {{ $year }} Simi. All rights reserved.</p>
    </div>
</div>
</body>
</html>
