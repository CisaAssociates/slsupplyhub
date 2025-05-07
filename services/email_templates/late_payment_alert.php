<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Late Payment Alert - SLSupplyHub</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2196F3;">SLSupplyHub</h1>
        </div>

        <div style="background-color: #fff3e0; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h2 style="color: #e65100; margin-top: 0;">⚠️ Late Payment Alert</h2>
            <p>This is a notification regarding a delayed COD payment collection for:</p>
        </div>

        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3>Order Details</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0;"><strong>Order ID:</strong></td>
                    <td style="padding: 8px 0;">#<?php echo htmlspecialchars($data['id']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Supplier:</strong></td>
                    <td style="padding: 8px 0;"><?php echo htmlspecialchars($data['supplier_name']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Delivery Driver:</strong></td>
                    <td style="padding: 8px 0;"><?php echo htmlspecialchars($data['driver_name'] ?: 'Not assigned'); ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Amount Due:</strong></td>
                    <td style="padding: 8px 0;">₱<?php echo number_format($data['total_amount'], 2); ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Delivery Date:</strong></td>
                    <td style="padding: 8px 0;"><?php echo date('F j, Y', strtotime($data['created_at'])); ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Time Elapsed:</strong></td>
                    <td style="padding: 8px 0;"><?php echo htmlspecialchars($data['hours_elapsed']); ?> hours</td>
                </tr>
            </table>
        </div>

        <div style="margin: 20px 0;">
            <h3>Required Actions</h3>
            <ul style="list-style-type: none; padding: 0;">
                <li style="margin-bottom: 10px;">✓ Contact the delivery driver immediately</li>
                <li style="margin-bottom: 10px;">✓ Verify payment collection status</li>
                <li style="margin-bottom: 10px;">✓ Update payment record in the system</li>
            </ul>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/order-detail.php?id=<?php echo urlencode($data['id']); ?>" 
               style="background-color: #2196F3; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                View Order Details
            </a>
        </div>

        <div style="margin-top: 20px; padding: 20px; background-color: #f5f5f5; border-radius: 5px;">
            <p style="margin: 0;"><strong>Note:</strong> All COD payments should be collected and recorded within 24 hours of successful delivery. 
            Please ensure this is addressed promptly to maintain smooth operations.</p>
        </div>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #666;">
            <p>This is an automated message, please do not reply.</p>
            <p>&copy; <?php echo date('Y'); ?> SLSupplyHub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>