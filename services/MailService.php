<?php
namespace SLSupplyHub;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class MailService {
    private $mailer;
    private $fromEmail;
    private $fromName;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->fromEmail = 'noreply@slsupplyhub.com';
        $this->fromName = 'SL Supply Hub';
        
        $this->initializeMailer();
    }

    private function initializeMailer() {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = getenv('SMTP_HOST') ?: 'smtp.mailtrap.io';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = getenv('SMTP_USERNAME');
            $this->mailer->Password = getenv('SMTP_PASSWORD');
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = getenv('SMTP_PORT') ?: 587;
            
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->isHTML(true);
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
}