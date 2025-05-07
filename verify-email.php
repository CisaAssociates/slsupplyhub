<?php
require_once "services/database.php";
require_once "services/user.php";
require_once "services/session.php";

use SLSupplyHub\{User, Session};

$session = new Session();
$user = new User($conn);

$verification_status = '';

if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    
    try {
        $result = $user->verifyEmail($token);
        
        if ($result['success']) {
            $verification_status = 'success';
        } else {
            $verification_status = 'error';
        }
    } catch (Exception $e) {
        $verification_status = 'error';
    }
} else {
    header("Location: auth-login.php");
    exit;
}
?>

<?php include 'partials/main.php'; ?>

<head>
    <?php
    $title = "Email Verification";
    include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php'; ?>
</head>

<body class="auth-fluid-pages pb-0">
    <div class="auth-fluid">
        <!--Auth fluid left content -->
        <div class="auth-fluid-form-box">
            <div class="align-items-center d-flex h-100">
                <div class="p-3">
                    <!-- Logo -->
                    <div class="auth-brand text-center text-lg-start">
                        <div class="auth-brand">
                            <a href="index.php" class="logo logo-dark text-center">
                                <span class="logo-lg">
                                    <img src="assets/images/logo-dark.png" alt="" height="22">
                                </span>
                            </a>
                            <a href="index.php" class="logo logo-light text-center">
                                <span class="logo-lg">
                                    <img src="assets/images/logo-light.png" alt="" height="22">
                                </span>
                            </a>
                        </div>
                    </div>

                    <div class="text-center">
                        <?php if ($verification_status === 'success'): ?>
                            <div class="mb-4">
                                <h1 class="text-success"><i class="mdi mdi-check-circle"></i></h1>
                                <h4>Email Verified Successfully!</h4>
                                <p class="text-muted">Your email address has been verified. You can now log in to your account.</p>
                            </div>
                            <a href="auth-login.php" class="btn btn-primary">Log In</a>
                        <?php else: ?>
                            <div class="mb-4">
                                <h1 class="text-danger"><i class="mdi mdi-close-circle"></i></h1>
                                <h4>Verification Failed</h4>
                                <p class="text-muted">The verification link is invalid or has expired. Please request a new verification email.</p>
                            </div>
                            <a href="auth-login.php" class="btn btn-primary">Back to Login</a>
                        <?php endif; ?>
                    </div>

                </div> <!-- end .card-body -->
            </div> <!-- end .align-items-center.d-flex.h-100-->
        </div>
        <!-- end auth-fluid-form-box-->

        <!-- Auth fluid right content -->
        <div class="auth-fluid-right text-center">
            <div class="auth-user-testimonial">
                <h2 class="mb-3 text-white">Email Verification</h2>
                <p class="lead"><i class="mdi mdi-format-quote-open"></i> Thank you for verifying your email address. This helps us ensure the security of your account. <i class="mdi mdi-format-quote-close"></i>
                </p>
            </div> <!-- end auth-user-testimonial-->
        </div>
        <!-- end Auth fluid right content -->
    </div>
    <!-- end auth-fluid-->

    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="assets/js/app.min.js"></script>

</body>
</html>