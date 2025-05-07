<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Loyalty Rewards Update - SLSupplyHub</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2196F3;">SLSupplyHub</h1>
        </div>

        <h2>Your Loyalty Rewards Update</h2>

        <div style="background-color: #fff3e0; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3 style="color: #e65100; margin-top: 0;">🌟 Congratulations!</h3>
            <p>Dear <?php echo htmlspecialchars($data['customer']['name']); ?>,</p>
            <p>Your loyalty status has been updated to:</p>
            
            <div style="text-align: center; margin: 20px 0;">
                <div style="font-size: 24px; font-weight: bold; color: #e65100;">
                    <?php echo htmlspecialchars($data['tier']); ?> Tier
                </div>
            </div>
        </div>

        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3>Your Rewards Summary</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0;">Total Transactions:</td>
                    <td style="padding: 8px 0;"><?php echo number_format($data['customer']['transaction_count']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;">Current Rewards Balance:</td>
                    <td style="padding: 8px 0;">₱<?php echo number_format($data['rewards']['reward_amount'], 2); ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;">Cashback Rate:</td>
                    <td style="padding: 8px 0;">
                        <?php
                        switch($data['tier']) {
                            case 'Gold':
                                echo '5%';
                                break;
                            case 'Silver':
                                echo '3%';
                                break;
                            default:
                                echo '0%';
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin: 20px 0;">
            <h3>Your Benefits</h3>
            <?php if ($data['tier'] === 'Gold'): ?>
                <ul style="list-style-type: none; padding: 0;">
                    <li style="margin-bottom: 10px;">✅ 5% cashback on all orders</li>
                    <li style="margin-bottom: 10px;">✅ Priority customer support</li>
                    <li style="margin-bottom: 10px;">✅ Special promotional offers</li>
                </ul>
            <?php elseif ($data['tier'] === 'Silver'): ?>
                <ul style="list-style-type: none; padding: 0;">
                    <li style="margin-bottom: 10px;">✅ 3% cashback on all orders</li>
                    <li style="margin-bottom: 10px;">✅ Special promotional offers</li>
                </ul>
            <?php endif; ?>

            <?php if ($data['tier'] !== 'Gold'): ?>
                <p>Continue shopping to reach Gold Tier (50+ transactions) and enjoy maximum benefits!</p>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/dashboard.php" 
               style="background-color: #2196F3; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                View Your Dashboard
            </a>
        </div>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #666;">
            <p>This is an automated message, please do not reply.</p>
            <p>&copy; <?php echo date('Y'); ?> SLSupplyHub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>