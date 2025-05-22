<?php include 'partials/main.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $title = "Application Status";
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
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <h4 class="page-title">Application Status</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body p-4">
                                    <?php if (isset($_SESSION['warning_message'])): ?>
                                        <div class="alert alert-warning" role="alert">
                                            <i class="mdi mdi-alert-circle-outline me-2"></i>
                                            <?php echo $_SESSION['warning_message']; unset($_SESSION['warning_message']); ?>
                                        </div>
                                    <?php elseif (isset($_SESSION['error_message'])): ?>
                                        <div class="alert alert-danger" role="alert">
                                            <i class="mdi mdi-block-helper me-2"></i>
                                            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info" role="alert">
                                            <i class="mdi mdi-information-outline me-2"></i>
                                            Your supplier application is being reviewed. You'll be notified once it's approved.
                                        </div>
                                    <?php endif; ?>

                                    <div class="text-center">
                                        <img src="<?= asset_url() ?>images/svg/maintenance.svg" alt="Pending Approval" class="img-fluid mb-4" style="max-height: 250px;">
                                        
                                        <h4 class="mt-3">Supplier Application Pending</h4>
                                        <p class="text-muted">
                                            Thank you for applying to become a supplier at SL Supply Hub. Our team is currently reviewing your application.
                                            You'll receive an email notification once your application has been processed.
                                        </p>
                                        
                                        <div class="mt-4">
                                            <h5>What happens next?</h5>
                                            <ol class="text-start">
                                                <li>Our team reviews your application and documents</li>
                                                <li>If approved, you'll receive an email confirmation</li>
                                                <li>You can then access your supplier dashboard</li>
                                                <li>Start listing your products and grow your business!</li>
                                            </ol>
                                        </div>
                                        
                                        <div class="mt-4 pt-2">
                                            <a href="<?= base_url() ?>" class="btn btn-primary">Return to Homepage</a>
                                        </div>
                                    </div>
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