<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify Your SLSupplyHub Account</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2196F3;">SLSupplyHub</h1>
        </div>
        
        <p>Dear <?php echo htmlspecialchars($data['name']); ?>,</p>
        
        <p>Welcome to SLSupplyHub! Please verify your email address to complete your registration.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="<?php echo htmlspecialchars($data['verifyUrl']); ?>" 
               style="background-color: #2196F3; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                Verify Email Address
            </a>
        </div>
        
        <p>If the button above doesn't work, copy and paste this link into your browser:</p>
        <p style="word-break: break-all;"><?php echo htmlspecialchars($data['verifyUrl']); ?></p>
        
        <p>If you didn't create an account with SLSupplyHub, please ignore this email.</p>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #666;">
            <p>This is an automated message, please do not reply.</p>
            <p>&copy; <?php echo date('Y'); ?> SLSupplyHub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>