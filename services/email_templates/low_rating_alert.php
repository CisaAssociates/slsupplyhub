<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Low Rating Alert - SLSupplyHub</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2196F3;">SLSupplyHub</h1>
        </div>

        <div style="background-color: #fff3e0; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h2 style="color: #e65100; margin-top: 0;">⚠️ Low Rating Alert</h2>
            <p>This is a notification regarding a recent low rating received for your business:</p>
        </div>

        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3>Feedback Details</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0;"><strong>Business:</strong></td>
                    <td style="padding: 8px 0;"><?php echo htmlspecialchars($data['business_name']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Order ID:</strong></td>
                    <td style="padding: 8px 0;">#<?php echo htmlspecialchars($data['order_id']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Rating:</strong></td>
                    <td style="padding: 8px 0;">
                        <?php 
                        $stars = str_repeat('★', $data['rating']) . str_repeat('☆', 5 - $data['rating']);
                        echo "<span style='color: #ffc107;'>$stars</span>";
                        ?>
                    </td>
                </tr>
                <?php if (!empty($data['comment'])): ?>
                <tr>
                    <td style="padding: 8px 0;"><strong>Customer Comment:</strong></td>
                    <td style="padding: 8px 0;"><?php echo htmlspecialchars($data['comment']); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <div style="margin: 20px 0;">
            <h3>Recommended Actions</h3>
            <ul style="list-style-type: none; padding: 0;">
                <li style="margin-bottom: 10px;">✓ Review order details and customer feedback</li>
                <li style="margin-bottom: 10px;">✓ Identify areas for improvement</li>
                <li style="margin-bottom: 10px;">✓ Consider reaching out to the customer</li>
                <li style="margin-bottom: 10px;">✓ Update business practices if necessary</li>
            </ul>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/order-detail.php?id=<?php echo urlencode($data['order_id']); ?>" 
               style="background-color: #2196F3; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                View Order Details
            </a>
        </div>

        <div style="margin-top: 20px; padding: 20px; background-color: #f5f5f5; border-radius: 5px;">
            <p style="margin: 0;"><strong>Note:</strong> Maintaining high customer satisfaction is crucial for business growth. 
            Please take time to address any concerns raised in this feedback.</p>
        </div>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
            <p>Need assistance improving your ratings? Our support team is here to help with best practices and guidance.</p>
        </div>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #666;">
            <p>This is an automated message, please do not reply.</p>
            <p>&copy; <?php echo date('Y'); ?> SLSupplyHub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>