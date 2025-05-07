<?php include 'partials/main.php'; ?>

<?php
use SLSupplyHub\User;
$user = new User();

$email = $password = "";
$email_err = $password_err = $login_err = "";

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate CSRF token
     if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $login_err = "Invalid request";
    } else {
        // Check if email is empty
        if(empty(trim($_POST["email"]))){
            $email_err = "Please enter email.";
        } else{
            $email = trim($_POST["email"]);
        }
        
        // Check if password is empty
        if(empty(trim($_POST["password"]))){
            $password_err = "Please enter your password.";
        } else{
            $password = trim($_POST["password"]);
        }
        
        // Validate credentials
        if(empty($email_err) && empty($password_err)){
            // Check for too many login attempts
            if (!$session->checkLoginAttempts($email)) {
                $remaining_time = $session->getLockoutTimeRemaining($email);
                $login_err = "Too many failed attempts. Please try again in " . ceil($remaining_time / 60) . " minutes.";
            } else {
                $result = $user->login($email, $password);
                
                if($result && !isset($result['error'])){ 
                    $session->resetLoginAttempts($email);
                    
                    $session->regenerate();
                    
                    if($session->login($result)){ 
                        $session->updateActivity();
                        
                        // Make sure we have a role before redirecting
                        if (isset($result['role'])) {
                            $user->redirectToDashboard($result['role']);
                            $session->isLoggedIn();
                        } else {
                            $login_err = "Invalid user role.";
                        }
                    } else {
                        $login_err = "Session initialization failed.";
                    }
                } else {
                    // Check if there's a specific error message
                    if (is_array($result) && isset($result['error'])) {
                        $login_err = $result['error'];
                    } else {
                        // Increment failed login attempts
                        $session->incrementLoginAttempts($email);
                        $remaining_attempts = $session->getRemainingAttempts($email);
                        
                        if ($remaining_attempts > 0) {
                            $login_err = "Invalid email or password. {$remaining_attempts} attempts remaining.";
                        } else {
                            $login_err = "Too many failed attempts. Account is temporarily locked.";
                        }
                    }
                }
            }
        }
    }
}

// Generate CSRF token for the form
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<head>
    <?php
    $title = "Log In";
    include 'partials/title-meta.php'; ?>

    <?php include 'partials/head-css.php'; ?>
</head>

<body class="auth-fluid-pages pb-0">
    <div class="auth-fluid">
        <!--Auth fluid left content -->
        <div class="auth-fluid-form-box">
            <div class="align-items-center d-flex h-100 justify-content-center">
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
                    <h4 class="mt-0">Sign In</h4>
                    <p class="text-muted mb-4">Enter your email address and password to access account.</p>

                    <?php 
                    if(!empty($login_err)){
                        echo '<div class="alert alert-danger">' . $login_err . '</div>';
                    }        
                    ?>

                    <!-- form -->
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="mb-3">
                            <label for="emailaddress" class="form-label">Email address</label>
                            <input class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                                   type="email" id="emailaddress" name="email" 
                                   value="<?php echo $email; ?>" 
                                   placeholder="Enter your email">
                            <span class="invalid-feedback"><?php echo $email_err; ?></span>
                        </div>
                        <div class="mb-3">
                            <a href="auth-recoverpw-2.php" class="text-muted float-end"><small>Forgot your password?</small></a>
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
                                <input type="checkbox" class="form-check-input" id="checkbox-signin" name="remember">
                                <label class="form-check-label" for="checkbox-signin">Remember me</label>
                            </div>
                        </div>
                        <div class="text-center d-grid">
                            <button class="btn btn-primary" type="submit">Log In</button>
                        </div>
                    </form>
                    <!-- end form-->

                    <!-- Footer-->
                    <footer class="footer footer-alt">
                        <p class="text-muted">Don't have an account? <a href="auth-register.php" class="text-muted ms-1"><b>Sign Up</b></a></p>
                    </footer>

                </div> <!-- end .card-body -->
            </div> <!-- end .align-items-center.d-flex.h-100-->
        </div>
        <!-- end auth-fluid-form-box-->

        <!-- Auth fluid right content -->
        <div class="auth-fluid-right text-center">
            <div class="auth-user-testimonial">
                <h2 class="mb-3 text-white"></h2>
                <p class="lead"><i class="mdi mdi-format-quote-open"></i><i class="mdi mdi-format-quote-close"></i>
                </p>
                <h5 class="text-white">

                
                </h5>
            </div> <!-- end auth-user-testimonial-->
        </div>
        <!-- end Auth fluid right content -->
    </div>
    <!-- end auth-fluid-->

    <!-- Authentication js -->
    <script src="<?php echo base_url(); ?>js/pages/authentication.init.js"></script>

</body>
</html>