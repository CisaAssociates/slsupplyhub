<?php
require_once "services/database.php";
require_once "services/user.php";
require_once "services/session.php";
require_once "services/mail.php";

use SLSupplyHub\{User, Session, MailService};

$session = new Session();
$user = new User();
$mail = new MailService();

$email = $email_err = "";
$reset_status = "";

// Generate CSRF token for the form
if (!$session->has('csrf_token')) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!$session->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $email_err = "Invalid request";
    } else {
        if (empty(trim($_POST["email"]))) {
            $email_err = "Please enter your email address.";
        } else {
            $email = trim($_POST["email"]);
            
            // Check if email exists
            $stmt = $conn->prepare("SELECT id, fullname FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user_data = $stmt->fetch();
            
            if ($user_data) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                
                try {
                    $conn->beginTransaction();
                    
                    // Store reset token
                    $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
                    $stmt->execute([$user_data['id'], $token]);
                    
                    // Send reset email
                    if ($mail->sendPasswordResetEmail($email, $user_data['fullname'], $token)) {
                        $conn->commit();
                        $reset_status = "success";
                    } else {
                        throw new Exception("Failed to send reset email");
                    }
                } catch (Exception $e) {
                    $conn->rollBack();
                    $email_err = "An error occurred. Please try again later.";
                }
            } else {
                // Don't reveal whether the email exists or not
                $reset_status = "success";
            }
        }
    }
}
?>

<?php include 'partials/main.php'; ?>

<head>
    <?php
    $title = "Forgot Password";
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

                        <!-- title-->
                        <h4 class="mt-0">Recover Password</h4>
                        <p class="text-muted mb-4">Enter your email address and we'll send you an email with instructions to reset your password.</p>

                        <?php if ($reset_status === "success"): ?>
                            <div class="alert alert-success">
                                If your email address exists in our database, you will receive a password recovery link at your email address in a few minutes.
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($email_err)): ?>
                            <div class="alert alert-danger"><?php echo $email_err; ?></div>
                        <?php endif; ?>

                        <!-- form -->
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                            <div class="mb-3">
                                <label for="emailaddress" class="form-label">Email address</label>
                                <input class="form-control" type="email" id="emailaddress" name="email" required="" placeholder="Enter your email">
                            </div>

                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <div class="text-center d-grid">
                                <button class="btn btn-primary waves-effect waves-light" type="submit"> Reset Password </button>
                            </div>

                        </form>
                        <!-- end form-->

                        <!-- Footer-->
                        <footer class="footer footer-alt">
                            <p class="text-muted">Back to <a href="auth-login-2.php" class="text-muted ms-1"><b>Log in</b></a></p>
                        </footer>

                    </div> <!-- end .card-body -->
                </div> <!-- end .align-items-center.d-flex.h-100-->
            </div>
            <!-- end auth-fluid-form-box-->

            <!-- Auth fluid right content -->
            <div class="auth-fluid-right text-center">
                <div class="auth-user-testimonial">
                    <h2 class="mb-3 text-white">I love the color!</h2>
                    <p class="lead"><i class="mdi mdi-format-quote-open"></i> I've been using your theme from the previous developer for our web app, once I knew new version is out, I immediately bought with no hesitation. Great themes, good documentation with lots of customization available and sample app that really fit our need. <i class="mdi mdi-format-quote-close"></i>
                    </p>
                    <h5 class="text-white">
                        - Fadlisaad (Ubold Admin User)
                    </h5>
                </div> <!-- end auth-user-testimonial-->
            </div>
            <!-- end Auth fluid right content -->
        </div>
        <!-- end auth-fluid-->

        <!-- Authentication js -->
        <script src="assets/js/pages/authentication.init.js"></script>

    </body>
</html>