<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Supplier Verification Status - SLSupplyHub</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2196F3;">SLSupplyHub</h1>
        </div>

        <h2>Supplier Verification Update</h2>

        <?php if ($data['verified']): ?>
            <div style="background-color: #e8f5e9; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h3 style="color: #2e7d32; margin-top: 0;">✅ Congratulations!</h3>
                <p>Dear <?php echo htmlspecialchars($data['name']); ?>,</p>
                <p>Your business has been verified successfully. You can now start selling on SLSupplyHub!</p>
                <p>Your customers will see a verified badge on your products, increasing trust and visibility.</p>
            </div>
            
            <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h3>Next Steps</h3>
                <ul style="list-style-type: none; padding: 0;">
                    <li style="margin-bottom: 10px;">📦 Start listing your products</li>
                    <li style="margin-bottom: 10px;">👥 Add delivery drivers to your team</li>
                    <li style="margin-bottom: 10px;">📊 Monitor your business analytics</li>
                </ul>
            </div>
        <?php else: ?>
            <div style="background-color: #ffebee; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h3 style="color: #c62828; margin-top: 0;">Verification Update</h3>
                <p>Dear <?php echo htmlspecialchars($data['name']); ?>,</p>
                <p>We regret to inform you that we could not verify your business at this time.</p>
                <p>Common reasons for this include:</p>
                <ul>
                    <li>Expired business permits</li>
                    <li>Unclear permit documentation</li>
                    <li>Missing required information</li>
                </ul>
                <p>Please review and update your business documentation and try again.</p>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin: 30px 0;">
            <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/supplier/dashboard.php" 
               style="background-color: #2196F3; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                Go to Dashboard
            </a>
        </div>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
            <p>If you have any questions or need assistance, please contact our support team.</p>
        </div>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #666;">
            <p>This is an automated message, please do not reply.</p>
            <p>&copy; <?php echo date('Y'); ?> SLSupplyHub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>