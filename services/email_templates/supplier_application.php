<?php
function getSupplierApplicationEmailTemplate($businessName) {
    return <<<HTML
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .content { margin-bottom: 30px; }
            .steps { background: #f5f5f5; padding: 20px; border-radius: 5px; }
            .footer { text-align: center; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>Thank You for Applying to SL Supply Hub</h2>
            </div>
            
            <div class="content">
                <p>Dear $businessName,</p>
                
                <p>Thank you for applying to become a supplier on SL Supply Hub. We have received your application and our team will review it within 1-2 business days.</p>
                
                <div class="steps">
                    <h3>What happens next?</h3>
                    <ol>
                        <li>Our team will review your submitted documents and business information</li>
                        <li>We may contact you for additional information if needed</li>
                        <li>Once approved, you'll receive another email with your login credentials</li>
                        <li>You can then start setting up your store and listing products</li>
                    </ol>
                </div>
                
                <p>If you have any questions in the meantime, please don't hesitate to contact our support team.</p>
            </div>
            
            <div class="footer">
                <p>Best regards,<br>SL Supply Hub Team</p>
            </div>
        </div>
    </body>
    </html>
    HTML;
}
?>
