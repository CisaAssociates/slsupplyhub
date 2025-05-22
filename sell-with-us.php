<?php
include 'partials/main.php';

use SLSupplyHub\MailService;
use SLSupplyHub\Database;

// Initialize variables
$submit_success = false;
$submit_error = '';

// Initialize form fields
$formData = [   
    'fullname' => '',
    'email' => '',
    'phone' => '',
    'business_name' => '',
    'business_address' => '',
    'business_email' => '',
    'business_phone' => '',
    'business_permit_number' => '',
    'tax_id' => ''
];

// Initialize errors
$errors = [
    'fullname' => '',
    'email' => '',
    'phone' => '',
    'business_name' => '',
    'business_address' => '',
    'business_email' => '',
    'business_phone' => '',
    'business_permit_number' => '',
    'tax_id' => '',
    'business_permit_file' => '',
    'tax_certificate_file' => '',
    'store_photos' => ''
];

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!$session->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $submit_error = "Invalid request";
        goto render_page;
    }

    // Form validation
    $isValid = true;

    // Validate fullname
    if (empty(trim($_POST["fullname"]))) {
        $errors['fullname'] = "Please enter your full name.";
        $isValid = false;
    } else {
        $formData['fullname'] = trim($_POST["fullname"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $errors['email'] = "Please enter an email.";
        $isValid = false;
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
        $isValid = false;
    } else {
        $formData['email'] = trim($_POST["email"]);
    }

    // Validate phone
    if (empty(trim($_POST["phone"]))) {
        $errors['phone'] = "Please enter a phone number.";
        $isValid = false;
    } else {
        $formData['phone'] = trim($_POST["phone"]);
    }

    // Validate business_name
    if (empty(trim($_POST["business_name"]))) {
        $errors['business_name'] = "Please enter your business name.";
        $isValid = false;
    } else {
        $formData['business_name'] = trim($_POST["business_name"]);
    }

    // Validate business_address
    if (empty(trim($_POST["business_address"]))) {
        $errors['business_address'] = "Please enter your business address.";
        $isValid = false;
    } else {
        $formData['business_address'] = trim($_POST["business_address"]);
    }

    // Validate business_email
    if (empty(trim($_POST["business_email"]))) {
        $errors['business_email'] = "Please enter your business email.";
        $isValid = false;
    } elseif (!filter_var(trim($_POST["business_email"]), FILTER_VALIDATE_EMAIL)) {
        $errors['business_email'] = "Invalid business email format.";
        $isValid = false;
    } else {
        $formData['business_email'] = trim($_POST["business_email"]);
    }

    // Validate business_phone
    if (empty(trim($_POST["business_phone"]))) {
        $errors['business_phone'] = "Please enter your business phone.";
        $isValid = false;
    } else {
        $formData['business_phone'] = trim($_POST["business_phone"]);
    }

    // Validate business_permit_number
    if (empty(trim($_POST["business_permit_number"]))) {
        $errors['business_permit_number'] = "Please enter your business permit number.";
        $isValid = false;
    } else {
        $formData['business_permit_number'] = trim($_POST["business_permit_number"]);
    }

    // Validate tax_id (optional)
    if (!empty(trim($_POST["tax_id"]))) {
        $formData['tax_id'] = trim($_POST["tax_id"]);
    }

    // Validate business_permit_file
    if (!isset($_FILES['business_permit_file']) || $_FILES['business_permit_file']['error'] == UPLOAD_ERR_NO_FILE) {
        $errors['business_permit_file'] = "Please upload your business permit.";
        $isValid = false;
    }

    // Validate tax_certificate_file (optional)
    // We don't require this file, but you can add validation if needed

    // Validate store_photos (optional)
    // We don't require store photos, but you can add validation if needed

    // If all fields are valid, process the supplier application
    if ($isValid) {
        try {
            // Upload business permit file
            $business_permit_file = null;
            if (isset($_FILES['business_permit_file']) && $_FILES['business_permit_file']['error'] == UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/documents/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $filename = 'business_permit_' . uniqid() . '.' . pathinfo($_FILES['business_permit_file']['name'], PATHINFO_EXTENSION);
                $destination = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['business_permit_file']['tmp_name'], $destination)) {
                    $business_permit_file = $destination;
                }
            }

            // Upload tax certificate file (if provided)
            $tax_certificate_file = null;
            if (isset($_FILES['tax_certificate_file']) && $_FILES['tax_certificate_file']['error'] == UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/documents/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $filename = 'tax_certificate_' . uniqid() . '.' . pathinfo($_FILES['tax_certificate_file']['name'], PATHINFO_EXTENSION);
                $destination = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['tax_certificate_file']['tmp_name'], $destination)) {
                    $tax_certificate_file = $destination;
                }
            }

            // Upload store photos (if provided)
            $store_photos = [];
            if (isset($_FILES['store_photos']) && $_FILES['store_photos']['error'][0] != UPLOAD_ERR_NO_FILE) {
                $upload_dir = 'uploads/stores/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $file_count = count($_FILES['store_photos']['name']);
                for ($i = 0; $i < $file_count; $i++) {
                    if ($_FILES['store_photos']['error'][$i] == UPLOAD_ERR_OK) {
                        $filename = 'store_photo_' . uniqid() . '.' . pathinfo($_FILES['store_photos']['name'][$i], PATHINFO_EXTENSION);
                        $destination = $upload_dir . $filename;

                        if (move_uploaded_file($_FILES['store_photos']['tmp_name'][$i], $destination)) {
                            $store_photos[] = $destination;
                        }
                    }
                }
            }

            $database = Database::getInstance();
            $db = $database->getConnection();
            $db->beginTransaction();

            // Check if email already exists
            $check_email_sql = "SELECT id, role FROM users WHERE email = ?";
            $check_stmt = $db->prepare($check_email_sql);
            $check_stmt->execute([$formData['email']]);
            $existing_user = $check_stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_user) {
                // User exists
                $user_id = $existing_user['id'];

                // Check if user is already a supplier
                if ($existing_user['role'] === 'supplier') {
                    // Check if supplier record exists
                    $check_supplier_sql = "SELECT id FROM suppliers WHERE user_id = ?";
                    $check_supplier_stmt = $db->prepare($check_supplier_sql);
                    $check_supplier_stmt->execute([$user_id]);

                    if ($check_supplier_stmt->fetch()) {
                        throw new Exception("An application for this email address has already been submitted.");
                    }
                } else {
                    // Update user role to supplier
                    $update_role_sql = "UPDATE users SET role = 'supplier', phone = ? WHERE id = ?";
                    $update_role_stmt = $db->prepare($update_role_sql);
                    $update_role_stmt->execute([$formData['phone'], $user_id]);
                }
            } else {
                // Create a new account with the role 'supplier'
                $userData = [
                    'fullname' => $formData['fullname'],
                    'email' => $formData['email'],
                    'phone' => $formData['phone'],
                    'role' => 'supplier'
                ];

                // Generate a random password and save both hashed and plain text versions
                $plainPassword = generateRandomString(12);
                $userData['password'] = password_hash($plainPassword, PASSWORD_DEFAULT);

                // Debug password generation
                error_log("Generated new password for supplier: " . substr($plainPassword, 0, 3) . '***');

                // Insert user
                $sql = "INSERT INTO users (fullname, email, password, phone, role) VALUES (?, ?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $userData['fullname'],
                    $userData['email'],
                    $userData['password'],
                    $userData['phone'],
                    $userData['role']
                ]);

                $user_id = $db->lastInsertId();
            }

            // Insert supplier
            $sql = "INSERT INTO suppliers (user_id, business_name, business_address, business_phone, business_email, business_permit_number, tax_id, business_permit_file, tax_certificate_file, store_photos_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $user_id,
                $formData['business_name'],
                $formData['business_address'],
                $formData['business_phone'],
                $formData['business_email'],
                $formData['business_permit_number'],
                $formData['tax_id'],
                $business_permit_file,
                $tax_certificate_file,
                json_encode($store_photos)
            ]);

            $db->commit();

            // Send confirmation email to supplier
            $mailService = new MailService();

            // Debug data being sent to email service
            error_log("Sending email with " . (isset($plainPassword) ? 'password' : 'NO password'));

            $mailService->sendSupplierApplicationConfirmation($formData['email'], [
                'name' => $formData['fullname'],
                'fullname' => $formData['fullname'],
                'business_name' => $formData['business_name'],
                'email' => $formData['email'],
                'password' => isset($plainPassword) ? $plainPassword : null // Include password if it was generated
            ]);

            // Send notification to admin
            $mailService->sendAdminSupplierApplicationNotification([
                'name' => $formData['fullname'],
                'business_name' => $formData['business_name'],
                'business_email' => $formData['business_email'],
                'business_phone' => $formData['business_phone']
            ]);

            // Store success message in session and redirect
            $_SESSION['application_success'] = true;
            header('Location: application-success.php');
            exit;
        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            error_log("Supplier application error: " . $e->getMessage());
            $submit_error = "An error occurred while processing your application: " . $e->getMessage();
        }
    }
}

// Helper function to generate a random string
function generateRandomString($length = 12)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

render_page:
// Generate CSRF token for the form
if (!$session->has('csrf_token')) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<head>
    <?php
    $title = "Sell with Us - Become a Supplier";
    include 'partials/title-meta.php'; ?>

    <!-- File Upload CSS -->
    <link href="<?= asset_url() ?>libs/dropzone/min/dropzone.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= asset_url() ?>libs/dropify/css/dropify.min.css" rel="stylesheet" type="text/css" />

    <?php include 'partials/head-css.php'; ?>
    <!-- Custom  sCss -->
    <link rel="stylesheet" type="text/css" href="assets/welcome/css/style.css" />
    <style>
        .form-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .form-section h4 {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .benefits-card {
            background-color: #ffffff;
            border-left: 4px solid #1abc9c;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .benefits-card i {
            color: #1abc9c;
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>

<body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top navbar-custom sticky-dark" id="sticky">
        <div class="container-fluid">
            <!-- LOGO -->
            <a class="logo text-uppercase" href="./">
                <img src="assets/welcome/images/logo-light.png" alt="SupplyHub" class="logo-light rounded-circle" height="60" />
                <img src="assets/welcome/images/logo-dark.png" alt="SupplyHub" class="logo-dark rounded-circle" height="60" />
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <i class="mdi mdi-menu"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav mx-auto navbar-end">
                </ul>
                <div>
                    <a href="auth-login.php" class="btn btn-light me-2">Login</a>
                    <a href="auth-register.php" class="btn btn-info">Sign Up</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->
    <!-- Begin page -->
    <div class="wrapper">
        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">

                    <div class="row mt-3">
                        <div class="col-lg-8">

                            <?php if (!empty($submit_error)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="mdi mdi-block-helper me-2"></i> <?php echo $submit_error; ?>
                                </div>
                            <?php endif; ?>

                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-3">Supplier Application Form</h4>

                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                                        <div class="form-section">
                                            <h4>Personal Information</h4>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="fullname" class="form-label">Full Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control <?php echo (!empty($errors['fullname'])) ? 'is-invalid' : ''; ?>"
                                                        id="fullname" name="fullname" value="<?php echo $formData['fullname']; ?>" required>
                                                    <?php if (!empty($errors['fullname'])): ?>
                                                        <div class="invalid-feedback"><?php echo $errors['fullname']; ?></div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control <?php echo (!empty($errors['email'])) ? 'is-invalid' : ''; ?>"
                                                        id="email" name="email" value="<?php echo $formData['email']; ?>" required>
                                                    <?php if (!empty($errors['email'])): ?>
                                                        <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control <?php echo (!empty($errors['phone'])) ? 'is-invalid' : ''; ?>"
                                                    id="phone" name="phone" value="<?php echo $formData['phone']; ?>" required>
                                                <?php if (!empty($errors['phone'])): ?>
                                                    <div class="invalid-feedback"><?php echo $errors['phone']; ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="form-section">
                                            <h4>Business Information</h4>
                                            <div class="mb-3">
                                                <label for="business_name" class="form-label">Business Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control <?php echo (!empty($errors['business_name'])) ? 'is-invalid' : ''; ?>"
                                                    id="business_name" name="business_name" value="<?php echo $formData['business_name']; ?>" required>
                                                <?php if (!empty($errors['business_name'])): ?>
                                                    <div class="invalid-feedback"><?php echo $errors['business_name']; ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mb-3">
                                                <label for="business_address" class="form-label">Business Address <span class="text-danger">*</span></label>
                                                <textarea class="form-control <?php echo (!empty($errors['business_address'])) ? 'is-invalid' : ''; ?>"
                                                    id="business_address" name="business_address" rows="3" required><?php echo $formData['business_address']; ?></textarea>
                                                <?php if (!empty($errors['business_address'])): ?>
                                                    <div class="invalid-feedback"><?php echo $errors['business_address']; ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="business_email" class="form-label">Business Email <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control <?php echo (!empty($errors['business_email'])) ? 'is-invalid' : ''; ?>"
                                                        id="business_email" name="business_email" value="<?php echo $formData['business_email']; ?>" required>
                                                    <?php if (!empty($errors['business_email'])): ?>
                                                        <div class="invalid-feedback"><?php echo $errors['business_email']; ?></div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="business_phone" class="form-label">Business Phone <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control <?php echo (!empty($errors['business_phone'])) ? 'is-invalid' : ''; ?>"
                                                        id="business_phone" name="business_phone" value="<?php echo $formData['business_phone']; ?>" required>
                                                    <?php if (!empty($errors['business_phone'])): ?>
                                                        <div class="invalid-feedback"><?php echo $errors['business_phone']; ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="business_permit_number" class="form-label">Business Permit Number <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control <?php echo (!empty($errors['business_permit_number'])) ? 'is-invalid' : ''; ?>"
                                                        id="business_permit_number" name="business_permit_number" value="<?php echo $formData['business_permit_number']; ?>" required>
                                                    <?php if (!empty($errors['business_permit_number'])): ?>
                                                        <div class="invalid-feedback"><?php echo $errors['business_permit_number']; ?></div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="tax_id" class="form-label">Tax ID (optional)</label>
                                                    <input type="text" class="form-control <?php echo (!empty($errors['tax_id'])) ? 'is-invalid' : ''; ?>"
                                                        id="tax_id" name="tax_id" value="<?php echo $formData['tax_id']; ?>">
                                                    <?php if (!empty($errors['tax_id'])): ?>
                                                        <div class="invalid-feedback"><?php echo $errors['tax_id']; ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-section">
                                            <h4>Document Upload</h4>
                                            <div class="mb-3">
                                                <label for="business_permit_file" class="form-label">Business Permit <span class="text-danger">*</span></label>
                                                <input type="file" class="dropify <?php echo (!empty($errors['business_permit_file'])) ? 'is-invalid' : ''; ?>"
                                                    data-height="150" id="business_permit_file" name="business_permit_file"
                                                    data-allowed-file-extensions="pdf jpg jpeg png" required>
                                                <small class="text-muted">Upload a copy of your business permit (PDF, JPG, JPEG, PNG)</small>
                                                <?php if (!empty($errors['business_permit_file'])): ?>
                                                    <div class="invalid-feedback d-block"><?php echo $errors['business_permit_file']; ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mb-3">
                                                <label for="tax_certificate_file" class="form-label">Tax Certificate (optional)</label>
                                                <input type="file" class="dropify" data-height="150" id="tax_certificate_file"
                                                    name="tax_certificate_file" data-allowed-file-extensions="pdf jpg jpeg png">
                                                <small class="text-muted">Upload a copy of your tax certificate (PDF, JPG, JPEG, PNG)</small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Store Photos (optional)</label>
                                                <div class="dropzone" id="store-photos-dropzone">
                                                    <div class="fallback">
                                                        <input name="store_photos[]" type="file" multiple />
                                                    </div>
                                                    <div class="dz-message needsclick">
                                                        <i class="h1 text-muted dripicons-cloud-upload"></i>
                                                        <h3>Drop files here or click to upload.</h3>
                                                        <span class="text-muted font-13">(Upload photos of your store or products. This helps us evaluate your application faster)</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="terms_agreement" name="terms_agreement" required>
                                                <label class="form-check-label" for="terms_agreement">
                                                    I agree to the <a href="#" target="_blank">Terms and Conditions</a> and <a href="#" target="_blank">Seller Policy</a> <span class="text-danger">*</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">Submit Application</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-3">Why Sell with Us?</h4>

                                    <div class="benefits-card">
                                        <i class="mdi mdi-account-group float-start"></i>
                                        <h5>Reach More Customers</h5>
                                        <p class="text-muted mb-0">Connect with thousands of customers searching for products like yours</p>
                                    </div>

                                    <div class="benefits-card">
                                        <i class="mdi mdi-truck-fast float-start"></i>
                                        <h5>Simplified Logistics</h5>
                                        <p class="text-muted mb-0">Our delivery network makes getting products to customers easy</p>
                                    </div>

                                    <div class="benefits-card">
                                        <i class="mdi mdi-chart-line float-start"></i>
                                        <h5>Business Growth</h5>
                                        <p class="text-muted mb-0">Access tools and analytics to help grow your business</p>
                                    </div>

                                    <div class="benefits-card">
                                        <i class="mdi mdi-shield-check float-start"></i>
                                        <h5>Secure Payments</h5>
                                        <p class="text-muted mb-0">Get paid reliably for every sale with our secure payment system</p>
                                    </div>

                                    <div class="benefits-card">
                                        <i class="mdi mdi-laptop float-start"></i>
                                        <h5>Easy to Manage</h5>
                                        <p class="text-muted mb-0">Intuitive dashboard to manage products, orders, and customers</p>
                                    </div>

                                    <div class="mt-4">
                                        <h5>How It Works</h5>
                                        <div class="text-center py-3">
                                            <img src="assets/images/sell-with-us-process.svg" alt="How it works" class="img-fluid" style="max-height: 250px;">
                                        </div>
                                        <ol class="ps-3">
                                            <li class="mb-2">Submit your application with required documents</li>
                                            <li class="mb-2">Our team reviews your application (2-3 business days)</li>
                                            <li class="mb-2">Once approved, set up your store profile</li>
                                            <li class="mb-2">Upload your products and start selling</li>
                                            <li>Receive orders and grow your business</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div> <!-- container -->

            </div> <!-- content -->
            <?php include 'partials/footer.php'; ?>

        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->
     

    <?php include 'partials/footer-scripts.php'; ?>

    <!-- File upload plugins -->
    <script src="<?= asset_url() ?>libs/dropzone/min/dropzone.min.js"></script>
    <script src="<?= asset_url() ?>libs/dropify/js/dropify.min.js"></script>

    <!-- Supplier Application JS -->
    <script src="<?= asset_url() ?>js/supplier-application.js"></script>

</body>

</html>