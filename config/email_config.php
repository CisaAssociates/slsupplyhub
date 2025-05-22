<?php
/**
 * Email Configuration
 * This file contains SMTP configuration for the email service
 */

return [
    // SMTP Configuration
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'markpeligro1234@gmail.com',  // Replace with your Gmail address
        'password' => 'qbsnytezwirkbkju',  // Replace with your Gmail app password (not regular password)
        'encryption' => 'tls',
        'from_email' => 'markpeligro1234@gmail.com',  // Should match the username above
        'from_name' => 'SL Supply Hub'
    ],
    
    // Admin Email Addresses
    'admin_email' => 'kmar0956@gmail.com',  // For testing, use an email you can access
    'support_email' => 'markpeligro1234@gmail.com',
    
    // Set to true to actually send emails in development
    'force_send_in_dev' => true
]; 