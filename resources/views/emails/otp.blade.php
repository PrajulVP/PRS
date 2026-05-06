<!DOCTYPE html>
<html>
<head>
    <title>Your Login OTP</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 80%; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .otp { font-size: 24px; font-weight: bold; color: #00497a; text-align: center; margin: 20px 0; letter-spacing: 5px; }
        .footer { font-size: 12px; color: #777; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Atom Connect</h2>
        </div>
        <p>Hello {{ $userName }},</p>
        <p>Your One-Time Password (OTP) for logging into the app is:</p>
        <div class="otp">
            {{ $otp }}
        </div>
        <p>This OTP is valid for 10 minutes. Please do not share this OTP with anyone.</p>
        <p>If you did not request this OTP, please ignore this email.</p>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Atom Connect. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
