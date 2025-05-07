<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Delivery Update - SLSupplyHub</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2196F3;">SLSupplyHub</h1>
        </div>

        <?php if ($data['status'] === 'successful'): ?>
            <div style="background-color: #e8f5e9; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h2 style="color: #2e7d32; margin-top: 0;">✅ Delivery Successful</h2>
                <p>Your order has been delivered successfully!</p>
            </div>
        <?php else: ?>
            <div style="background-color: #ffebee; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h2 style="color: #c62828; margin-top: 0;">⚠️ Delivery Failed</h2>
                <p>Unfortunately, there was an issue with your delivery.</p>
                <?php if (!empty($data['failure_reason'])): ?>
                    <p><strong>Reason:</strong> <?php echo htmlspecialchars($data['failure_reason']); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3>Order Details</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0;"><strong>Order ID:</strong></td>
                    <td style="padding: 8px 0;">#<?php echo htmlspecialchars($data['id']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Supplier:</strong></td>
                    <td style="padding: 8px 0;"><?php echo htmlspecialchars($data['business_name']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Amount:</strong></td>
                    <td style="padding: 8px 0;">₱<?php echo number_format($data['total_amount'], 2); ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Updated:</strong></td>
                    <td style="padding: 8px 0;"><?php echo date('F j, Y g:i A', strtotime($data['updated_at'])); ?></td>
                </tr>
            </table>
        </div>

        <?php if ($data['status'] === 'successful'): ?>
            <div style="margin: 20px 0;">
                <h3>Next Steps</h3>
                <ul style="list-style-type: none; padding: 0;">
                    <li style="margin-bottom: 10px;">✓ Rate your delivery experience</li>
                    <li style="margin-bottom: 10px;">✓ Leave feedback for the supplier</li>
                    <li style="margin-bottom: 10px;">✓ Check your loyalty rewards status</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/order-detail.php?id=<?php echo urlencode($data['id']); ?>&feedback=true" 
                   style="background-color: #2196F3; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                    Rate & Review
                </a>
            </div>
        <?php else: ?>
            <div style="margin: 20px 0;">
                <h3>Support</h3>
                <p>If you have any questions about this delivery, please contact our support team or the supplier directly.</p>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/order-detail.php?id=<?php echo urlencode($data['id']); ?>" 
                   style="background-color: #2196F3; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                    View Order Details
                </a>
            </div>
        <?php endif; ?>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
            <p>If you have any questions or concerns, please don't hesitate to contact our support team.</p>
        </div>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #666;">
            <p>This is an automated message, please do not reply.</p>
            <p>&copy; <?php echo date('Y'); ?> SLSupplyHub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

<?php
function getDeliveryUpdateEmailTemplate($data) {
    $statusMessages = [
        'pending' => 'Your order is being prepared',
        'processing' => 'Your order is being processed',
        'out_for_delivery' => 'Your order is out for delivery',
        'delivered' => 'Your order has been delivered successfully',
        'failed' => 'There was an issue with your delivery',
        'cancelled' => 'Your order has been cancelled'
    ];

    $statusMessage = $statusMessages[$data['status']] ?? 'Order status has been updated';
    $failureNote = $data['failure_reason'] ? "<p style='color: #dc3545;'>Reason: {$data['failure_reason']}</p>" : '';

    return "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
        <h2 style='color: #333;'>Order Update</h2>
        <p>Dear {$data['name']},</p>
        
        <p><strong>{$statusMessage}</strong></p>
        {$failureNote}
        
        <div style='background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px;'>
            <h3 style='margin-top: 0;'>Order Details</h3>
            <p><strong>Order ID:</strong> #{$data['id']}</p>
            <p><strong>Seller:</strong> {$data['business_name']}</p>
            
            <h4>Delivery Address:</h4>
            <p>
                {$data['recipient_name']}<br>
                {$data['contact_number']}<br>
                {$data['address_line']}<br>
                {$data['barangay']}, {$data['municipality']}<br>
                {$data['province']}<br>
                " . ($data['landmark'] ? "Landmark: {$data['landmark']}" : "") . "
            </p>
        </div>
        
        <p>If you have any questions about your delivery, please contact our support team.</p>
        
        <p>Thank you for choosing our service!</p>
        
        <div style='margin-top: 30px; font-size: 12px; color: #666;'>
            <p>This is an automated message, please do not reply directly to this email.</p>
        </div>
    </div>";
}