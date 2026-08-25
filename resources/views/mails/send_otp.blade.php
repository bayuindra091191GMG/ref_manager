<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simi - Secure OTP Verification</title>
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
            font-size: 24px;
            font-weight: bold;
            color: #007BFF; /* Corporate color */
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
            <img src="https://karada.goodhrapp.com/images/logo_karada_orange.png" alt="karada" width="150" />
        </div>
        <div class="content">
            <h2>Hello {{ $user->name }},</h2>
            <p>Your OTP code is:</p>
            <div class="otp-code">{{ $otp_code }}</div>
            <p>{{ $msg }}</p>
        </div>
        <div class="footer">
            <p>&copy; {{ $year }} Simi. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
