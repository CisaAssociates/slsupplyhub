<?php
require_once 'partials/main.php';
use SLSupplyHub\MailService;

// Check if the form was submitted
$message = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['test_email'])) {
    try {
        $testEmail = $_POST['test_email'];
        
        // Basic validation
        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        // Create mail service instance
        $mailService = new MailService();
        
        // Send a test email
        $result = $mailService->sendVerificationEmail(
            $testEmail, 
            'Test User', 
            'test-token-' . bin2hex(random_bytes(8))
        );
        
        if ($result) {
            $success = true;
            $message = "Test email successfully sent to {$testEmail}. Please check your inbox (and spam folder).";
        } else {
            $message = "Failed to send test email. Check server logs for more details.";
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $title = "Email Test";
    include 'partials/title-meta.php'; 
    include 'partials/head-css.php';
    ?>
</head>
<body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
    <div class="wrapper">
        <?php include 'partials/topbar.php'; ?>
        
        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <div class="row pt-4">
                        <div class="col-12">
                            <div class="page-title-box">
                                <h4 class="page-title">Email Test Tool</h4>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-3">Test Gmail Email Configuration</h4>
                                    
                                    <?php if ($message): ?>
                                        <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>" role="alert">
                                            <?php echo $message; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <form method="post" action="">
                                        <div class="mb-3">
                                            <label for="test_email" class="form-label">Email Address to Test</label>
                                            <input type="email" class="form-control" id="test_email" name="test_email" 
                                                   required placeholder="Enter an email address to receive the test message">
                                            <div class="form-text">
                                                This will send a verification email to test your Gmail configuration.
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Send Test Email</button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Configuration Instructions</h4>
                                    <p>Before testing, make sure you've updated your email configuration:</p>
                                    <ol>
                                        <li>Set up a Gmail App Password as described in GMAIL_EMAIL_SETUP.md</li>
                                        <li>Update config/email_config.php with your Gmail credentials</li>
                                        <li>Use this form to verify the configuration works</li>
                                    </ol>
                                    <div class="alert alert-info">
                                        If you encounter issues, check your server logs for detailed error messages.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Current Email Configuration</h4>
                                    <p>These are the current SMTP settings from your config file:</p>
                                    
                                    <?php
                                    $config = require 'config/email_config.php';
                                    $smtp = $config['smtp'];
                                    ?>
                                    
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <th>SMTP Host</th>
                                                <td><?php echo htmlspecialchars($smtp['host']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>SMTP Port</th>
                                                <td><?php echo htmlspecialchars($smtp['port']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Username</th>
                                                <td><?php echo htmlspecialchars($smtp['username']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Password</th>
                                                <td><span class="text-muted">[hidden for security]</span></td>
                                            </tr>
                                            <tr>
                                                <th>Encryption</th>
                                                <td><?php echo htmlspecialchars($smtp['encryption']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>From Email</th>
                                                <td><?php echo htmlspecialchars($smtp['from_email']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>From Name</th>
                                                <td><?php echo htmlspecialchars($smtp['from_name']); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include 'partials/footer.php'; ?>
        </div>
    </div>
    
    <?php include 'partials/footer-scripts.php'; ?>
</body>
</html> 