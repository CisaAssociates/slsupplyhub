<?php include 'partials/main.php'; ?>
<head>
    <?php
    $title = "403 Forbidden";
    include 'partials/title-meta.php';
    include 'partials/head-css.php';
    ?>
</head>

<body>
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-lg-5">
                    <div class="card">
                        <div class="card-header pt-4 pb-4 text-center bg-danger">
                            <a href="">
                                <span><img src="assets/images/logo.png" alt="logo" height="42"></span>
                            </a>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center">
                                <h1 class="text-error">403</h1>
                                <h4 class="text-uppercase text-danger mt-3">Access Forbidden</h4>
                                <p class="text-muted mt-3">
                                    You do not have permission to access this page.
                                    Please contact an administrator if you believe this is an error.
                                </p>

                                <a class="btn btn-info mt-3" href=""><i class="mdi mdi-reply"></i> Return Home</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'partials/footer-scripts.php'; ?>
</body>

</html>