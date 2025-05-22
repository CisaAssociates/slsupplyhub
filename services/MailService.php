<?php
namespace SLSupplyHub;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class MailService {
    private $mailer;
    private $fromEmail;
    private $fromName;
    private $config;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        // Load email configuration
        $configFile = __DIR__ . '/../config/email_config.php';
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        } else {
            $this->config = [
                'smtp' => [
                    'host' => 'smtp.mailtrap.io',
                    'port' => 2525,
                    'username' => 'your_mailtrap_username',
                    'password' => 'your_mailtrap_password',
                    'encryption' => 'tls',
                    'from_email' => 'noreply@slsupplyhub.com',
                    'from_name' => 'SL Supply Hub'
                ],
                'force_send_in_dev' => false
            ];
        }
        
        $this->fromEmail = $this->config['smtp']['from_email'];
        $this->fromName = $this->config['smtp']['from_name'];
        
        $this->initializeMailer();
    }

    private function initializeMailer() {
        try {
            // Use configuration values
            $mailHost = $this->config['smtp']['host'];
            $mailUsername = $this->config['smtp']['username'];
            $mailPassword = $this->config['smtp']['password'];
            $mailEncryption = $this->config['smtp']['encryption'];
            $mailPort = $this->config['smtp']['port'];
                
            // Configure mailer for Gmail
                $this->mailer->isSMTP();
                $this->mailer->Host = $mailHost;
                $this->mailer->SMTPAuth = true;
                $this->mailer->Username = $mailUsername;
                $this->mailer->Password = $mailPassword;
                $this->mailer->SMTPSecure = $mailEncryption;
                $this->mailer->Port = $mailPort;
                
            // Gmail requires these additional settings
            $this->mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
            
                $this->mailer->setFrom($this->fromEmail, $this->fromName);
                $this->mailer->isHTML(true);
            
            // Log that real email is being used
            error_log("Email service initialized with Gmail SMTP settings. Emails will be sent using Gmail.");
        } catch (Exception $e) {
            error_log("Mail initialization error: " . $e->getMessage());
            throw new Exception("Failed to initialize mail service");
        }
    }

    public function sendVerificationEmail($email, $name, $token) {
        try {
            $verificationLink = "http://{$_SERVER['HTTP_HOST']}/verify-email.php?token=" . $token;
            
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = 'Verify Your Email - SL Supply Hub';
            
            $body = file_get_contents(__DIR__ . '/email_templates/verification.html');
            $body = str_replace(
                ['{{name}}', '{{verification_link}}'],
                [$name, $verificationLink],
                $body
            );
            
            $this->mailer->Body = $body;
            
            $this->mailer->send();
            error_log("Sent verification email to {$email}");
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to send verification email: " . $e->getMessage());
            return false;
        }
    }

    public function sendPasswordResetEmail($email, $name, $token) {
        try {
            $resetLink = "http://{$_SERVER['HTTP_HOST']}/reset-password.php?token=" . $token;
            
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = 'Reset Your Password - SL Supply Hub';
            
            $body = file_get_contents(__DIR__ . '/email_templates/password_reset.html');
            $body = str_replace(
                ['{{name}}', '{{reset_link}}'],
                [$name, $resetLink],
                $body
            );
            
            $this->mailer->Body = $body;
            
            $this->mailer->send();
            error_log("Sent password reset email to {$email}");
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to send password reset email: " . $e->getMessage());
            return false;
        }
    }

    public function sendOrderConfirmation($orderDetails, $customerEmail, $customerName) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($customerEmail, $customerName);
            $this->mailer->Subject = 'Order Confirmation - SL Supply Hub';
            
            $body = file_get_contents(__DIR__ . '/email_templates/order_confirmation.html');
            $body = str_replace(
                [
                    '{{name}}',
                    '{{order_id}}',
                    '{{order_date}}',
                    '{{order_total}}',
                    '{{order_items}}'
                ],
                [
                    $customerName,
                    $orderDetails['id'],
                    $orderDetails['created_at'],
                    $orderDetails['total_amount'],
                    $this->generateOrderItemsHtml($orderDetails['items'])
                ],
                $body
            );
            
            $this->mailer->Body = $body;
            
            $this->mailer->send();
            error_log("Sent order confirmation email to {$customerEmail}");
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to send order confirmation email: " . $e->getMessage());
            return false;
        }
    }

    private function generateOrderItemsHtml($items) {
        $html = '<table style="width:100%; border-collapse: collapse;">';
        $html .= '<tr><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr>';
        
        foreach ($items as $item) {
            $html .= sprintf(
                '<tr><td>%s</td><td>%d</td><td>₱%.2f</td><td>₱%.2f</td></tr>',
                htmlspecialchars($item['name']),
                $item['quantity'],
                $item['price'],
                $item['quantity'] * $item['price']
            );
        }
        
        $html .= '</table>';
        return $html;
    }

    public function sendOrderStatusUpdate($orderDetails, $customerEmail, $customerName, $newStatus) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($customerEmail, $customerName);
            $this->mailer->Subject = 'Order Status Update - SL Supply Hub';
            
            $body = file_get_contents(__DIR__ . '/email_templates/order_status_update.html');
            $body = str_replace(
                [
                    '{{name}}',
                    '{{order_id}}',
                    '{{new_status}}',
                    '{{status_message}}'
                ],
                [
                    $customerName,
                    $orderDetails['id'],
                    ucfirst($newStatus),
                    $this->getStatusMessage($newStatus)
                ],
                $body
            );
            
            $this->mailer->Body = $body;
            
            $this->mailer->send();
            error_log("Sent order status update email to {$customerEmail}");
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to send order status update email: " . $e->getMessage());
            return false;
        }
    }

    private function getStatusMessage($status) {
        $messages = [
            'confirmed' => 'Your order has been confirmed and is being processed.',
            'processing' => 'Your order is now being prepared for shipping.',
            'shipped' => 'Your order has been shipped and is on its way to you.',
            'delivered' => 'Your order has been delivered successfully.',
            'cancelled' => 'Your order has been cancelled as requested.'
        ];
        
        return $messages[$status] ?? 'Your order status has been updated.';
    }

    public function sendSupplierApplicationConfirmation($email, $data) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $data['fullname'] ?? $data['name'] ?? 'Supplier');
            $this->mailer->Subject = 'Supplier Application Received - SL Supply Hub';
            
            $body = file_get_contents(__DIR__ . '/email_templates/supplier_application.html');
            
            // Debug logging
            error_log("Password in email data: " . (isset($data['password']) ? 'YES' : 'NO'));
            if (isset($data['password'])) {
                error_log("Password value: " . substr($data['password'], 0, 3) . '***');
            }
            
            // Handle password conditionals in template
            if (isset($data['password']) && !empty($data['password'])) {
                error_log("Including password section in email template");
                
                // Simple replacement approach for the conditional section
                $passwordSection = '
                <div class="credentials">
                    <h4>Your Account Credentials</h4>
                    <p>Email: <strong>' . htmlspecialchars($email) . '</strong></p>
                    <p>Password: <span class="password">' . htmlspecialchars($data['password']) . '</span></p>
                    <p class="warning">Please save this password. You will need it to login once your application is approved.</p>
                </div>';
                
                // Replace the conditional section with the actual content
                $body = preg_replace('/{{#if_password}}.*?{{\/if_password}}/s', $passwordSection, $body);
                
                // Basic replacements
                $replacements = [
                    '{{fullname}}' => $data['fullname'] ?? $data['name'] ?? 'Supplier',
                    '{{business_name}}' => $data['business_name']
                ];
            } else {
                error_log("Removing password section from email template");
                // Remove conditional section entirely
                $body = preg_replace('/{{#if_password}}.*?{{\/if_password}}/s', '', $body);
                
                // Basic replacements
                $replacements = [
                    '{{fullname}}' => $data['fullname'] ?? $data['name'] ?? 'Supplier',
                    '{{business_name}}' => $data['business_name']
                ];
            }
            
            // Apply all replacements
            foreach ($replacements as $key => $value) {
                $body = str_replace($key, $value, $body);
            }
            
            $this->mailer->Body = $body;
            
            $this->mailer->send();
            if (isset($data['password'])) {
                error_log("Sent supplier application confirmation email with credentials to {$email}");
            } else {
                error_log("Sent supplier application confirmation email to {$email}");
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to send supplier application confirmation email: " . $e->getMessage());
            return false;
        }
    }

    public function sendSupplierApprovalNotification($email, $data) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $data['fullname']);
            $this->mailer->Subject = 'Your Supplier Application Has Been Approved - SL Supply Hub';
            
            $loginUrl = "http://{$_SERVER['HTTP_HOST']}/auth-login.php";
            
            $body = file_get_contents(__DIR__ . '/email_templates/supplier_approval.html');
            $body = str_replace(
                [
                    '{{fullname}}',
                    '{{business_name}}',
                    '{{login_url}}'
                ],
                [
                    $data['fullname'],
                    $data['business_name'],
                    $loginUrl
                ],
                $body
            );
            
            $this->mailer->Body = $body;
            
            $this->mailer->send();
            error_log("Sent supplier approval notification email to {$email}");
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to send supplier approval email: " . $e->getMessage());
            return false;
        }
    }

    public function sendSupplierRejectionNotification($email, $data) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $data['fullname']);
            $this->mailer->Subject = 'Supplier Application Status - SL Supply Hub';
            
            $supportEmail = $this->config['support_email'] ?? 'support@slsupplyhub.com';
            
            $body = file_get_contents(__DIR__ . '/email_templates/supplier_rejection.html');
            $body = str_replace(
                [
                    '{{fullname}}',
                    '{{business_name}}',
                    '{{reason}}',
                    '{{support_email}}'
                ],
                [
                    $data['fullname'],
                    $data['business_name'],
                    $data['reason'] ?: 'Your application did not meet our current requirements.',
                    $supportEmail
                ],
                $body
            );
            
            $this->mailer->Body = $body;
            
            $this->mailer->send();
            error_log("Sent supplier rejection notification email to {$email}");
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to send supplier rejection email: " . $e->getMessage());
            return false;
        }
    }

    public function sendAdminSupplierApplicationNotification($data) {
        try {
            $adminEmail = $this->config['admin_email'] ?? 'admin@slsupplyhub.com';
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($adminEmail, 'SL Supply Hub Admin');
            $this->mailer->Subject = 'New Supplier Application - ' . $data['business_name'];
            
            $adminUrl = "http://{$_SERVER['HTTP_HOST']}/admin/supplier-approvals.php";
            
            $body = file_get_contents(__DIR__ . '/email_templates/admin_notification.html');
            $body = str_replace(
                [
                    '{{business_name}}',
                    '{{business_email}}',
                    '{{business_phone}}',
                    '{{business_type}}',
                    '{{admin_url}}'
                ],
                [
                    $data['business_name'],
                    $data['business_email'],
                    $data['business_phone'],
                    $data['business_type'] ?? 'N/A',
                    $adminUrl
                ],
                $body
            );
            
            $this->mailer->Body = $body;
            
            $this->mailer->send();
            error_log("Sent admin notification email about supplier application from {$data['business_name']}");
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to send admin notification email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Determines whether an email should actually be sent
     * based on environment and configuration
     */
    private function shouldSendEmail() {
        // Always return true to send emails in all environments
        return true;
    }
}