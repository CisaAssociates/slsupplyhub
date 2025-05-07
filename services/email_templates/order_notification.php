<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Order Update - SLSupplyHub</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2196F3;">SLSupplyHub</h1>
        </div>

        <?php if ($data['userType'] === 'customer'): ?>
            <h2>Order Confirmation - #<?php echo htmlspecialchars($data['order']['id']); ?></h2>
            <p>Thank you for your order! Here are your order details:</p>
        <?php elseif ($data['userType'] === 'supplier'): ?>
            <h2>New Order Received - #<?php echo htmlspecialchars($data['order']['id']); ?></h2>
            <p>You have received a new order. Please process it as soon as possible:</p>
        <?php else: ?>
            <h2>New Delivery Assignment - Order #<?php echo htmlspecialchars($data['order']['id']); ?></h2>
            <p>You have been assigned a new delivery:</p>
        <?php endif; ?>

        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3>Order Details</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0;">Order ID:</td>
                    <td style="padding: 8px 0;">#<?php echo htmlspecialchars($data['order']['id']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;">Date:</td>
                    <td style="padding: 8px 0;"><?php echo date('F j, Y', strtotime($data['order']['created_at'])); ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;">Total Amount:</td>
                    <td style="padding: 8px 0;">₱<?php echo number_format($data['order']['total_amount'], 2); ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;">Delivery Fee:</td>
                    <td style="padding: 8px 0;">₱<?php echo number_format($data['order']['delivery_fee'], 2); ?></td>
                </tr>
            </table>

            <?php if ($data['userType'] === 'driver'): ?>
                <div style="margin-top: 20px;">
                    <h3>Delivery Address</h3>
                    <p>
                        <?php echo htmlspecialchars($data['order']['recipient_name']); ?><br>
                        <?php echo htmlspecialchars($data['order']['address_line']); ?><br>
                        <?php echo htmlspecialchars($data['order']['barangay']); ?>,
                        <?php echo htmlspecialchars($data['order']['municipality']); ?><br>
                        <?php echo htmlspecialchars($data['order']['province']); ?>
                    </p>
                    <?php if (!empty($data['order']['landmark'])): ?>
                        <p><strong>Landmark:</strong> <?php echo htmlspecialchars($data['order']['landmark']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/order-detail.php?id=<?php echo urlencode($data['order']['id']); ?>" 
               style="background-color: #2196F3; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                View Order Details
            </a>
        </div>

        <?php if ($data['userType'] === 'customer'): ?>
            <p>We'll notify you when your order is out for delivery.</p>
        <?php elseif ($data['userType'] === 'supplier'): ?>
            <p>Please assign a driver and prepare the order for delivery.</p>
        <?php else: ?>
            <p>Please update the delivery status once completed.</p>
        <?php endif; ?>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #666;">
            <p>This is an automated message, please do not reply.</p>
            <p>&copy; <?php echo date('Y'); ?> SLSupplyHub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>