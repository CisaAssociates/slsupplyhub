<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Loyalty Tier Upgrade - SLSupplyHub</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2196F3;">SLSupplyHub</h1>
        </div>

        <div style="background-color: #e3f2fd; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h2 style="color: #1976D2; margin-top: 0;">🌟 Congratulations <?php echo htmlspecialchars($data['name']); ?>!</h2>
            <p>You've achieved <?php echo htmlspecialchars($data['tier']); ?> tier status in our loyalty program!</p>
            <p>Thank you for your continued support and trust in SLSupplyHub.</p>
        </div>

        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3>Your New Benefits</h3>
            <ul style="list-style-type: none; padding: 0;">
                <li style="margin-bottom: 15px; display: flex; align-items: center;">
                    <span style="color: #2196F3; margin-right: 10px;">✓</span>
                    <strong><?php echo number_format($data['discount_rate']); ?>% discount</strong> on all orders
                </li>
                <li style="margin-bottom: 15px; display: flex; align-items: center;">
                    <span style="color: #2196F3; margin-right: 10px;">✓</span>
                    <strong>Free delivery</strong> on orders over ₱<?php echo number_format($data['free_delivery_threshold'], 2); ?>
                </li>
                <li style="margin-bottom: 15px; display: flex; align-items: center;">
                    <span style="color: #2196F3; margin-right: 10px;">✓</span>
                    <strong>Priority support</strong> access
                </li>
                <?php if ($data['tier'] === 'Gold'): ?>
                <li style="margin-bottom: 15px; display: flex; align-items: center;">
                    <span style="color: #2196F3; margin-right: 10px;">✓</span>
                    <strong>Early access</strong> to special promotions
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <div style="background: #fff3e0; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3 style="color: #f57c00;">💡 Tips to Make the Most of Your Benefits</h3>
            <ul style="list-style-type: none; padding: 0;">
                <li style="margin-bottom: 10px;">• Plan your orders to meet the free delivery threshold</li>
                <li style="margin-bottom: 10px;">• Keep an eye on your email for exclusive promotions</li>
                <li style="margin-bottom: 10px;">• Share your experience with friends and family</li>
            </ul>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/customer/rewards.php" 
               style="background-color: #2196F3; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                View Your Rewards Dashboard
            </a>
        </div>

        <?php if ($data['tier'] === 'Silver'): ?>
        <div style="margin-top: 20px; padding: 20px; background-color: #f5f5f5; border-radius: 5px;">
            <p style="margin: 0;"><strong>Next Goal:</strong> Reach Gold tier by completing <?php echo self::TIERS['Gold']['min_transactions']; ?> successful orders 
            to unlock even more benefits including higher discounts and early access to promotions!</p>
        </div>
        <?php endif; ?>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
            <p>Thank you for choosing SLSupplyHub. We're committed to providing you with the best shopping experience.</p>
        </div>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #666;">
            <p>Want to learn more about our loyalty program? Visit our <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/loyalty-program" style="color: #2196F3; text-decoration: none;">loyalty program page</a>.</p>
            <p>&copy; <?php echo date('Y'); ?> SLSupplyHub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>