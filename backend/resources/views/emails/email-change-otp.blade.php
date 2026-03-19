<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        .header {
            background-color: #16a34a;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background-color: white;
            padding: 20px;
            border-radius: 0 0 8px 8px;
        }
        .otp-box {
            background-color: #f0fdf4;
            border: 2px solid #16a34a;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #16a34a;
            letter-spacing: 5px;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Email Change Verification</h1>
        </div>
        <div class="content">
            <p>Hello {{ $userName }},</p>
            
            <p>You have requested to change your email address. To complete this process, please use the verification code below:</p>
            
            <div class="otp-box">
                <p style="margin: 0 0 10px 0; color: #666;">Your Verification Code:</p>
                <div class="otp-code">{{ $otp }}</div>
            </div>
            
            <p><strong>Important:</strong></p>
            <ul>
                <li>This code will expire in 10 minutes</li>
                <li>Do not share this code with anyone</li>
                <li>If you did not request this change, please ignore this email</li>
            </ul>
            
            <p>If you have any questions, please contact our support team.</p>
            
            <p>Best regards,<br>Event Management System Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} CVSU Event Management System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
