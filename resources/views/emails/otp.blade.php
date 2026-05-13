<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Security OTP</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f4f7f9; 
            margin: 0; 
            padding: 0; 
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f7f9;
            padding-bottom: 40px;
        }
        .main-card {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-top: 5px solid #00497a;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .header {
            padding: 40px 0 30px;
            text-align: center;
            background-color: #ffffff;
        }
        .content {
            padding: 0 40px 40px;
            color: #4a5568;
            line-height: 1.6;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
        }
        .instruction {
            font-size: 15px;
            margin-bottom: 30px;
        }
        .otp-box {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }
        .otp-code {
            font-size: 36px;
            font-weight: 800;
            color: #00497a;
            letter-spacing: 12px;
            margin: 0;
            padding-left: 12px; /* Offset to center the spaced letters */
        }
        .security-note {
            font-size: 13px;
            color: #718096;
            background-color: #fffaf0;
            border-left: 4px solid #f6ad55;
            padding: 12px;
            margin-bottom: 30px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #a0aec0;
            padding: 20px;
        }
        .social-links { margin-top: 15px; }
        .logo-img { max-height: 60px; margin-bottom: 10px; }
        
        @media only screen and (max-width: 600px) {
            .main-card { border-radius: 0; border-top-width: 4px; }
            .content { padding: 0 20px 30px; }
            .otp-code { font-size: 28px; letter-spacing: 8px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Atom Connect Logo" class="logo-img">
            <div style="font-size: 20px; font-weight: 700; color: #00497a;">Atom Connect</div>
        </div>
        
        <div class="main-card">
            <div class="content">
                <div style="padding-top: 30px;">
                    <p class="greeting">Hello {{ $userName }},</p>
                    <p class="instruction">We received a request to access your account. Please use the following one-time password (OTP) to complete your login:</p>
                </div>
                
                <div class="otp-box">
                    <div class="otp-code">{{ $otp }}</div>
                </div>
                
                <div class="security-note">
                    <strong>Security Notice:</strong> This code is valid for <strong>10 minutes</strong>. For your security, please do not share this code with anyone. Our staff will never ask for your OTP.
                </div>
                
                <p style="font-size: 14px;">If you did not initiate this login attempt, please secure your account or contact our support team immediately.</p>
                
                <div style="margin-top: 40px; border-top: 1px solid #edf2f7; padding-top: 20px; font-size: 14px;">
                    Best Regards,<br>
                    <strong>Security Team</strong><br>
                    Atomed Wellness Private Limited
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Atomed Wellness. All rights reserved.</p>
            <p>You received this email because it is a security-related notification for your account.</p>
        </div>
    </div>
</body>
</html>
