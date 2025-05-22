<?php
session_start();

// If there's no success message in the session, redirect to home
if (!isset($_SESSION['application_success'])) {
    header('Location: index.php');
    exit;
}

// Clear the success message from session
unset($_SESSION['application_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted Successfully - SL Supply Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        .success-container {
            text-align: center;
            padding: 50px 20px;
            max-width: 600px;
            margin: 0 auto;
        }
        .success-icon {
            color: #28a745;
            font-size: 64px;
            margin-bottom: 20px;
        }
        .next-steps {
            text-align: left;
            margin-top: 30px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="success-container">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1 class="mb-4">Application Submitted Successfully!</h1>
            
            <p class="lead mb-4">
                Thank you for applying to become a supplier on SL Supply Hub. 
                We have received your application and will review it shortly.
            </p>
            
            <div class="next-steps">
                <h3>What happens next?</h3>
                <ol>
                    <li>Our team will review your application and documents (2-3 business days)</li>
                    <li>You will receive an email with the decision</li>
                    <li>If approved, you'll get access to your supplier dashboard</li>
                    <li>You can then start setting up your store and listing products</li>
                </ol>
            </div>
            
            <p class="mt-4">
                Have questions? Contact our support team at 
                <a href="mailto:support@slsupplyhub.com">support@slsupplyhub.com</a>
            </p>
            
            <a href="index.php" class="btn btn-primary mt-4">Return to Home</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/fontawesome.min.js"></script>
</body>
</html>
