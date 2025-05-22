<!DOCTYPE html>
<html>
<head>
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
        }
        .header {
            background: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background: #fff;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Application Received</h1>
        </div>
        
        <div class="content">
            <h2>Thank you for applying to become a supplier!</h2>
            
            <p>Dear {{SHOP_NAME}},</p>
            
            <p>We have received your supplier application and it is currently under review. Our team will carefully evaluate your submission and get back to you within 2-3 business days.</p>
            
            <h3>Next Steps:</h3>
            <ol>
                <li>Our team will review your business information and documents</li>
                <li>We may contact you if we need additional information</li>
                <li>You will receive an email notification about the status of your application</li>
            </ol>
            
            <p>If you have any questions in the meantime, please don't hesitate to contact our support team at support@slsupplyhub.com.</p>
            
            <p>Best regards,<br>
            SL Supply Hub Team</p>
        </div>
        
        <div class="footer">
            <p>This is an automated message, please do not reply to this email.</p>
            <p>&copy; 2023 SL Supply Hub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
