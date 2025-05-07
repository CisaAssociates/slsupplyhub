<?php
include 'partials/main.php';

use SLSupplyHub\User;

$user = new User();

// Initialize variables
$fullname = $email = $password = "";
$fullname_err = $email_err = $password_err = $register_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!$session->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $register_err = "Invalid request";
        goto render_page;
    }

    // Validate fullname
    if (empty(trim($_POST["fullname"]))) {
        $fullname_err = "Please enter your full name.";
    } elseif (!preg_match("/^[a-zA-Z .'-]+$/", trim($_POST["fullname"]))) {
        $fullname_err = "Only letters, spaces, apostrophes, periods, and hyphens allowed.";
    } else {
        $fullname = trim($_POST["fullname"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    } else {
        if ($user->emailExists(trim($_POST["email"]))) {
            $email_err = "This email is already registered.";
        } else {
            $email = trim($_POST["email"]);
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 8) {
        $password_err = "Password must have at least 8 characters.";
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", trim($_POST["password"]))) {
        $password_err = "Password must contain at least one uppercase letter, one lowercase letter, one number and one special character.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Check for errors
    if (empty($fullname_err) && empty($email_err) && empty($password_err)) {
        // Create registration data array
        $registrationData = [
            'fullname' => $fullname,
            'email' => $email,
            'password' => $password
        ];

        $result = $user->register($registrationData);
        
        if ($result['success']) {
            header("location: auth-login.php");
            exit;
        } else {
            $register_err = $result['error'] ?? "Registration failed. Please try again.";
        }
    }
}

render_page:
// Generate CSRF token for the form
if (!$session->has('csrf_token')) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<head>
    <?php
    $title = "Register & Signup";
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
                                    <img src="assets/images/logo-dark.png" alt="" height="80" class="rounded-circle">
                                </span>
                            </a>

                            <a href="index.php" class="logo logo-light text-center">
                                <span class="logo-lg">
                                    <img src="assets/images/logo-light.png" alt="" height="80" class="rounded-circle">
                                </span>
                            </a>
                        </div>
                    </div>

                    <!-- title-->
                    <h4 class="mt-0">Sign Up</h4>
                    <p class="text-muted mb-4">Don't have an account? Create your account, it takes less than a minute</p>

                    <!-- Add this after the <h4> and <p> tags -->
                    <?php if (!empty($register_err)): ?>
                        <div class="alert alert-danger"><?php echo $register_err; ?></div>
                    <?php endif; ?>

                    <!-- form -->
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input class="form-control <?php echo (!empty($fullname_err)) ? 'is-invalid' : ''; ?>"
                                type="text" id="fullname" name="fullname"
                                value="<?php echo $fullname; ?>"
                                placeholder="Enter your name">
                            <span class="invalid-feedback"><?php echo $fullname_err; ?></span>
                        </div>
                        <div class="mb-3">
                            <label for="emailaddress" class="form-label">Email address</label>
                            <input class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                                type="email" id="emailaddress" name="email"
                                value="<?php echo $email; ?>"
                                placeholder="Enter your email">
                            <span class="invalid-feedback"><?php echo $email_err; ?></span>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" name="password"
                                    class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                                    placeholder="Enter your password">
                                <div class="input-group-text" data-password="false">
                                    <span class="password-eye"></span>
                                </div>
                                <span class="invalid-feedback"><?php echo $password_err; ?></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="checkbox-signup" required>
                                <label class="form-check-label" for="checkbox-signup">I accept <a href="javascript: void(0);" class="text-dark">Terms and Conditions</a></label>
                            </div>
                        </div>
                        <div class="text-center d-grid">
                            <button class="btn btn-primary waves-effect waves-light" type="submit"> Sign Up </button>
                        </div>
                    </form>
                    <!-- end form-->

                    <!-- Footer-->
                    <footer class="footer footer-alt">
                        <p class="text-muted">Already have account? <a href="auth-login.php" class="text-muted ms-1"><b>Log In</b></a></p>
                    </footer>

                </div> <!-- end .card-body -->
            </div> <!-- end .align-items-center.d-flex.h-100-->
        </div>
        <!-- end auth-fluid-form-box-->

        <!-- Auth fluid right content -->
        <div class="auth-fluid-right text-center">
            <div class="auth-user-testimonial">
                <h2 class="mb-3 text-white">Join Our Growing Marketplace!</h2>
                <p class="lead"><i class="mdi mdi-format-quote-open"></i> SLSupplyHub has transformed how I manage my business. The platform makes it easy to connect with customers and grow my sales. The seamless ordering system and reliable delivery network have helped me expand my reach significantly. <i class="mdi mdi-format-quote-close"></i>
                </p>
                <h5 class="text-white">
                    - Sarah Chen, Successful Vendor
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