<?php
require_once "services/database.php";
require_once "services/user.php";
require_once "services/session.php";

use SLSupplyHub\{User, Session};

$session = new Session();
$user = new User($conn);

// Initialize error variable
$reset_err = "";

// Process reset token if provided in URL
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    
    // Verify token validity
    $stmt = $conn->prepare("SELECT user_id, expires_at, used FROM password_resets WHERE token = ? AND used = false");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    
    if (!$reset) {
        $reset_err = "Invalid or expired reset token.";
    } else if (strtotime($reset['expires_at']) < time()) {
        $reset_err = "This reset link has expired. Please request a new one.";
    }
} else {
    header("Location: auth-login.php");
    exit;
}

// Generate CSRF token for the form if not exists
if (!$session->has('csrf_token')) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($reset_err)) {
    // Validate CSRF token
    if (!$session->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $reset_err = "Invalid request";
    } else {
        $new_password = trim($_POST["new_password"]);
        $confirm_password = trim($_POST["confirm_password"]);
        
        // Validate password
        if (empty($new_password)) {
            $reset_err = "Please enter a new password.";
        } elseif (strlen($new_password) < 6) {
            $reset_err = "Password must have at least 6 characters.";
        } elseif ($new_password != $confirm_password) {
            $reset_err = "Passwords do not match.";
        } else {
            // Update password and mark token as used
            try {
                $conn->beginTransaction();
                
                // Update password
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt->execute([$hashed_password, $reset['user_id']]);
                
                // Mark token as used
                $stmt = $conn->prepare("UPDATE password_resets SET used = true WHERE token = ?");
                $stmt->execute([$token]);
                
                $conn->commit();
                
                // Redirect to login page with success message
                header("Location: auth-login.php?reset=success");
                exit;
            } catch (Exception $e) {
                $conn->rollBack();
                $reset_err = "An error occurred. Please try again.";
            }
        }
    }
}
?>

<?php include 'partials/main.php'; ?>

<head>
    <?php
    $title = "Reset Password";
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

                    <h4 class="mt-0">Reset Password</h4>
                    <p class="text-muted mb-4">Enter your new password below.</p>

                    <?php if (!empty($reset_err)): ?>
                        <div class="alert alert-danger"><?php echo $reset_err; ?></div>
                    <?php endif; ?>

                    <!-- form -->
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?token=" . $token); ?>" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="new_password" name="new_password" class="form-control" 
                                       placeholder="Enter your new password" required>
                                <div class="input-group-text" data-password="false">
                                    <span class="password-eye"></span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                       placeholder="Confirm your new password" required>
                                <div class="input-group-text" data-password="false">
                                    <span class="password-eye"></span>
                                </div>
                            </div>
                        </div>

                        <div class="text-center d-grid">
                            <button class="btn btn-primary" type="submit">Reset Password</button>
                        </div>
                    </form>
                    <!-- end form-->

                    <!-- Footer-->
                    <footer class="footer footer-alt">
                        <p class="text-muted">Back to <a href="auth-login.php" class="text-muted ms-1"><b>Log in</b></a></p>
                    </footer>

                </div> <!-- end .card-body -->
            </div> <!-- end .align-items-center.d-flex.h-100-->
        </div>
        <!-- end auth-fluid-form-box-->

        <!-- Auth fluid right content -->
        <div class="auth-fluid-right text-center">
            <div class="auth-user-testimonial">
                <h2 class="mb-3 text-white">Secure Password Reset</h2>
                <p class="lead"><i class="mdi mdi-format-quote-open"></i> Choose a strong password to keep your account secure. <i class="mdi mdi-format-quote-close"></i>
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