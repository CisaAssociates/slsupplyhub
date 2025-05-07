<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>SLSupply Hub - Your One-Stop Ecommerce Solution</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A modern ecommerce platform for all your shopping needs" name="description" />
    <meta content="SupplyHub" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/welcome/favicon.ico">

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="assets/welcome/css/bootstrap.min.css" type="text/css">

    <!--Material Icon -->
    <link rel="stylesheet" type="text/css" href="assets/welcome/css/materialdesignicons.min.css" />

    <!-- Custom  sCss -->
    <link rel="stylesheet" type="text/css" href="assets/welcome/css/style.css" />

</head>

<body data-bs-spy="scroll" data-bs-target=".navbar" data-bs-offset="78">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top navbar-custom sticky-dark" id="sticky">
        <div class="container-fluid">
            <!-- LOGO -->
            <a class="logo text-uppercase" href="javascript:void(0);">
                <img src="assets/welcome/images/logo-light.png" alt="SupplyHub" class="logo-light rounded-circle" height="60" />
                <img src="assets/welcome/images/logo-dark.png" alt="SupplyHub" class="logo-dark rounded-circle" height="60" />
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <i class="mdi mdi-menu"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav mx-auto navbar-center">
                    <li class="nav-item">
                        <a href="#home" class="nav-link">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="#categories" class="nav-link">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a href="#featured" class="nav-link">Featured</a>
                    </li>
                    <li class="nav-item">
                        <a href="#features" class="nav-link">Features</a>
                    </li>
                    <li class="nav-item">
                        <a href="#deals" class="nav-link">Deals</a>
                    </li>
                    <li class="nav-item">
                        <a href="#faq" class="nav-link">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a href="#testimonials" class="nav-link">Reviews</a>
                    </li>
                    <li class="nav-item">
                        <a href="#contact" class="nav-link">Contact</a>
                    </li>
                </ul>
                <div>
                    <a href="auth-login.php" class="btn btn-light me-2">Login</a>
                    <a href="auth-register.php" class="btn btn-info">Sign Up</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->

    <!-- start hero -->
    <section class="bg-home bg-light d-flex align-items-center" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <div class="mb-4 pb-3">
                        <h1 class="display-4 fw-semibold mb-3 text-light">Discover Amazing Products at 
                            <span class="text-info">Great Prices</span></h1>
                        <p class="lead text-muted mb-4">Shop from thousands of verified sellers and find exactly what you need. Free shipping on orders over ₱50!</p>
                        <div class="d-flex gap-2">
                            <a href="customer/index.php" class="btn btn-info">Shop Now</a>
                            <a href="supplier/dashboard.php" class="btn btn-light">Sell with Us</a>
                        </div>
                    </div>
                    <div class="d-flex gap-4 mb-4">
                        <div>
                            <h3 class="mb-1 text-light">50K+</h3>
                            <p class="text-muted mb-0">Products</p>
                        </div>
                        <div>
                            <h3 class="mb-1 text-light">5K+</h3>
                            <p class="text-muted mb-0">Sellers</p>
                        </div>
                        <div>
                            <h3 class="mb-1 text-light">100K+</h3>
                            <p class="text-muted mb-0">Happy Customers</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <?php include 'partials/home-img.php'; ?>
                </div>
            </div>
        </div>
    </section>
    <!-- end hero -->

    <!-- categories start -->
    <section class="section" id="categories">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="text-center mb-5">
                        <h3>Popular Categories</h3>
                        <p class="text-muted">Browse our most popular shopping categories</p>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-xl-3 col-sm-6">
                    <div class="card category-card">
                        <a href="customer/index.php?category=electronics" class="text-body">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <span class="avatar avatar-lg bg-primary-subtle">
                                            <i class="mdi mdi-laptop fs-2 text-primary"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-1">Electronics</h5>
                                        <p class="text-muted mb-0">3.5k Products</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                
                <div class="col-xl-3 col-sm-6">
                    <div class="card category-card">
                        <a href="customer/index.php?category=fashion" class="text-body">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <span class="avatar avatar-lg bg-success-subtle">
                                            <i class="mdi mdi-hanger fs-2 text-success"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-1">Fashion</h5>
                                        <p class="text-muted mb-0">2.8k Products</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="card category-card">
                        <a href="customer/index.php?category=home" class="text-body">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <span class="avatar avatar-lg bg-info-subtle">
                                            <i class="mdi mdi-home fs-2 text-info"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-1">Home & Living</h5>
                                        <p class="text-muted mb-0">1.9k Products</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="card category-card">
                        <a href="customer/index.php?category=sports" class="text-body">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <span class="avatar avatar-lg bg-warning-subtle">
                                            <i class="mdi mdi-basketball fs-2 text-warning"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-1">Sports</h5>
                                        <p class="text-muted mb-0">1.2k Products</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- categories end -->

    <!-- featured products start -->
    <section class="section bg-light" id="featured">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="text-center mb-4">
                        <h3>Featured Products</h3>
                        <p class="text-muted">Hand-picked products from our best sellers</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="card product-card mb-4">
                        <div class="product-badge bg-danger">Sale</div>
                        <img src="assets/images/products/product-9.jpg" class="card-img-top" alt="Product 1">
                        <div class="card-body">
                            <h5 class="card-title">Wireless Earbuds</h5>
                            <div class="product-rating text-warning mb-2">
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star-half"></i>
                                <span class="text-muted ms-2">(45 Reviews)</span>
                            </div>
                            <div class="product-price mb-3">
                                <span class="text-danger me-2">₱89.99</span>
                                <small class="text-muted text-decoration-line-through">₱129.99</small>
                            </div>
                            <a href="customer/product-detail.php?id=1" class="btn btn-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card product-card mb-4">
                        <div class="product-badge bg-success">New</div>
                        <img src="assets/images/products/product-10.jpg" class="card-img-top" alt="Product 2">
                        <div class="card-body">
                            <h5 class="card-title">Smart Watch</h5>
                            <div class="product-rating text-warning mb-2">
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <span class="text-muted ms-2">(32 Reviews)</span>
                            </div>
                            <div class="product-price mb-3">
                                <span class="text-dark">₱199.99</span>
                            </div>
                            <a href="customer/product-detail.php?id=2" class="btn btn-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card product-card mb-4">
                        <img src="assets/images/products/product-5.jpg" class="card-img-top" alt="Product 3">
                        <div class="card-body">
                            <h5 class="card-title">Designer Backpack</h5>
                            <div class="product-rating text-warning mb-2">
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <span class="text-muted ms-2">(28 Reviews)</span>
                            </div>
                            <div class="product-price mb-3">
                                <span class="text-dark">₱79.99</span>
                            </div>
                            <a href="customer/product-detail.php?id=3" class="btn btn-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card product-card mb-4">
                        <div class="product-badge bg-danger">Hot</div>
                        <img src="assets/images/products/product-3.jpg" class="card-img-top" alt="Product 4">
                        <div class="card-body">
                            <h5 class="card-title">Air Purifier</h5>
                            <div class="product-rating text-warning mb-2">
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star-half"></i>
                                <span class="text-muted ms-2">(56 Reviews)</span>
                            </div>
                            <div class="product-price mb-3">
                                <span class="text-danger me-2">₱149.99</span>
                                <small class="text-muted text-decoration-line-through">₱199.99</small>
                            </div>
                            <a href="customer/product-detail.php?id=4" class="btn btn-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 text-center mt-4">
                    <a href="customer/index.php" class="btn btn-outline-primary">View All Products <i class="mdi mdi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </section>
    <!-- featured products end -->

    <!-- clients start -->
    <section>
        <div class="container-fluid">
            <div class="clients p-4 bg-white">
                <div class="row">
                    <div class="col-md-3">
                        <div class="client-images">
                            <img src="assets/welcome/images/clients/1.png" alt="logo-img" class="mx-auto img-fluid d-block">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="client-images">
                            <img src="assets/welcome/images/clients/3.png" alt="logo-img" class="mx-auto img-fluid d-block">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="client-images">
                            <img src="assets/welcome/images/clients/4.png" alt="logo-img" class="mx-auto img-fluid d-block">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="client-images">
                            <img src="assets/welcome/images/clients/6.png" alt="logo-img" class="mx-auto img-fluid d-block">
                        </div>
                    </div>
                </div> <!-- end row -->
            </div>
        </div> <!-- end container-fluid -->
    </section>
    <!-- clients end -->

    <!-- features start -->
    <section class="section-sm" id="features">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="text-center mb-4 pb-1">
                        <h3 class="mb-3">Why Shop with Supply Hub</h3>
                        <p class="text-muted">Experience seamless shopping with our comprehensive ecommerce platform offering quality products, secure payments, and fast delivery.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="features-box">
                        <div class="features-img mb-4">
                            <i class="mdi mdi-shield h1 text-primary"></i>
                        </div>
                        <h4 class="mb-2">Secure Shopping</h4>
                        <p class="text-muted">Shop with confidence using our secure payment gateway and SSL encryption for all transactions.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="features-box">
                        <div class="features-img mb-4">
                            <i class="mdi mdi-truck-fast h1 text-primary"></i>
                        </div>
                        <h4 class="mb-2">Fast Delivery</h4>
                        <p class="text-muted">Get your orders delivered quickly with our reliable shipping partners and real-time order tracking.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="features-box">
                        <div class="features-img mb-4">
                            <i class="mdi mdi-star h1 text-primary"></i>
                        </div>
                        <h4 class="mb-2">Quality Products</h4>
                        <p class="text-muted">Browse through our curated collection of high-quality products from verified suppliers.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="features-box">
                        <div class="features-img mb-4">
                            <i class="mdi mdi-account-multiple h1 text-primary"></i>
                        </div>
                        <h4 class="mb-3">24/7 Support</h4>
                        <p class="text-muted">Our dedicated customer support team is always ready to help you with any questions or concerns.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="features-box">
                        <div class="features-img mb-4">
                            <i class="mdi mdi-wallet h1 text-primary"></i>
                        </div>
                        <h4 class="mb-3">Best Prices</h4>
                        <p class="text-muted">Get competitive prices and great deals on all your favorite products.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="features-box">
                        <div class="features-img mb-4">
                            <i class="mdi mdi-gift h1 text-primary"></i>
                        </div>
                        <h4 class="mb-3">Rewards Program</h4>
                        <p class="text-muted">Earn points with every purchase and enjoy exclusive member benefits.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- features end -->

    <!-- special deals start -->
    <section class="section" id="deals">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="text-center mb-4">
                        <h3>Special Deals</h3>
                        <p class="text-muted">Limited time offers you don't want to miss!</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card deal-card mb-4">
                        <div class="row g-0">
                            <div class="col-md-6">
                                <img src="assets/images/products/product-8.png" class="img-fluid rounded-start h-100" alt="Flash Sale">
                            </div>
                            <div class="col-md-6">
                                <div class="card-body">
                                    <div class="deal-badge bg-danger">Flash Sale</div>
                                    <h4 class="card-title">24-Hour Flash Sale</h4>
                                    <p class="card-text">Up to 70% off on selected electronics! Don't miss out on these incredible savings.</p>
                                    <div class="countdown mb-3">
                                        <span id="flash-sale-timer" class="text-danger">23:59:59</span> remaining
                                    </div>
                                    <a href="customer/index.php?sale=flash" class="btn btn-danger">Shop Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card deal-card mb-4">
                        <div class="row g-0">
                            <div class="col-md-6">
                                <img src="assets/images/products/product-12.jpg" class="img-fluid rounded-start h-100" alt="Bundle Offer">
                            </div>
                            <div class="col-md-6">
                                <div class="card-body">
                                    <div class="deal-badge bg-success">Bundle Deal</div>
                                    <h4 class="card-title">Smart Home Bundle</h4>
                                    <p class="card-text">Get 30% off when you buy 3 or more smart home devices. Perfect for home automation!</p>
                                    <div class="features-list mb-3">
                                        <small class="text-muted">
                                            <i class="mdi mdi-check-circle text-success"></i> Smart Speakers
                                            <br><i class="mdi mdi-check-circle text-success"></i> Security Cameras
                                            <br><i class="mdi mdi-check-circle text-success"></i> Smart Lights
                                        </small>
                                    </div>
                                    <a href="customer/index.php?bundle=smart-home" class="btn btn-success">View Bundle</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="card coupon-card mb-4">
                        <div class="card-body text-center">
                            <div class="coupon-icon mb-3">
                                <i class="mdi mdi-tag-multiple h1 text-primary"></i>
                            </div>
                            <h5 class="card-title">New Customer Special</h5>
                            <p class="card-text">Get 15% off your first purchase</p>
                            <div class="coupon-code bg-light p-2 mb-3">
                                <code>WELCOME15</code>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" onclick="copyCode('WELCOME15')">Copy Code</button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card coupon-card mb-4">
                        <div class="card-body text-center">
                            <div class="coupon-icon mb-3">
                                <i class="mdi mdi-truck-fast h1 text-primary"></i>
                            </div>
                            <h5 class="card-title">Free Shipping</h5>
                            <p class="card-text">Free shipping on orders over ₱50</p>
                            <div class="coupon-code bg-light p-2 mb-3">
                                <code>FREESHIP50</code>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" onclick="copyCode('FREESHIP50')">Copy Code</button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card coupon-card mb-4">
                        <div class="card-body text-center">
                            <div class="coupon-icon mb-3">
                                <i class="mdi mdi-cash-multiple h1 text-primary"></i>
                            </div>
                            <h5 class="card-title">Bulk Purchase</h5>
                            <p class="card-text">Save 20% on orders over ₱200</p>
                            <div class="coupon-code bg-light p-2 mb-3">
                                <code>BULK20</code>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" onclick="copyCode('BULK20')">Copy Code</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .deal-card {
                overflow: hidden;
                transition: transform 0.3s ease;
            }

            .deal-card:hover {
                transform: translateY(-5px);
            }

            .deal-badge {
                position: absolute;
                top: 10px;
                right: 10px;
                padding: 5px 10px;
                color: white;
                border-radius: 3px;
                font-size: 12px;
                font-weight: bold;
            }

            .countdown {
                font-size: 1.2em;
                font-weight: bold;
            }

            .coupon-card {
                transition: transform 0.3s ease;
            }

            .coupon-card:hover {
                transform: translateY(-5px);
            }

            .coupon-code {
                border-radius: 4px;
                font-size: 1.2em;
                letter-spacing: 2px;
            }
        </style>

        <script>
            function copyCode(code) {
                navigator.clipboard.writeText(code);
                // You could add a tooltip or notification here
                alert('Coupon code copied!');
            }

            // Simple countdown timer
            function updateTimer() {
                const timerElement = document.getElementById('flash-sale-timer');
                if (!timerElement) return;

                let time = timerElement.innerHTML;
                let [hours, minutes, seconds] = time.split(':').map(Number);

                if (seconds > 0) {
                    seconds--;
                } else {
                    if (minutes > 0) {
                        minutes--;
                        seconds = 59;
                    } else {
                        if (hours > 0) {
                            hours--;
                            minutes = 59;
                            seconds = 59;
                        }
                    }
                }

                timerElement.innerHTML = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }

            setInterval(updateTimer, 1000);
        </script>
    </section>
    <!-- special deals end -->

    <!-- faqs start -->
    <section class="section" id="faq">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="text-center mb-5">
                        <h3>Frequently Asked Questions</h3>
                        <p class="text-muted">Everything you need to know about shopping with Supply Hub</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-5 offset-lg-1">
                    <div>
                        <div class="faq-question-q-box">Q.</div>
                        <h4 class="faq-question">How do I place an order?</h4>
                        <p class="faq-answer mb-4 pb-1 text-muted">Browse our products, add items to your cart, and proceed to checkout. You can pay using various payment methods including credit cards, PayPal, and bank transfers.</p>
                    </div>

                    <div>
                        <div class="faq-question-q-box">Q.</div>
                        <h4 class="faq-question">What are the shipping options?</h4>
                        <p class="faq-answer mb-4 pb-1 text-muted">We offer standard shipping (3-5 business days), express shipping (1-2 business days), and same-day delivery for select areas. Shipping costs vary based on location and delivery speed.</p>
                    </div>

                    <div>
                        <div class="faq-question-q-box">Q.</div>
                        <h4 class="faq-question">How can I track my order?</h4>
                        <p class="faq-answer mb-4 pb-1 text-muted">Once your order ships, you'll receive a tracking number via email. You can also track your order through your account dashboard on our website.</p>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div>
                        <div class="faq-question-q-box">Q.</div>
                        <h4 class="faq-question">What is your return policy?</h4>
                        <p class="faq-answer mb-4 pb-1 text-muted">We offer a 30-day return policy for most items. Products must be unused and in original packaging. Some restrictions apply to certain categories.</p>
                    </div>

                    <div>
                        <div class="faq-question-q-box">Q.</div>
                        <h4 class="faq-question">How do I contact customer support?</h4>
                        <p class="faq-answer mb-4 pb-1 text-muted">Our customer support team is available 24/7 via live chat, email, or phone. Premium members get priority support with dedicated service representatives.</p>
                    </div>

                    <div>
                        <div class="faq-question-q-box">Q.</div>
                        <h4 class="faq-question">Is my payment information secure?</h4>
                        <p class="faq-answer mb-4 pb-1 text-muted">Yes, we use industry-standard SSL encryption to protect your payment information. We are PCI compliant and never store your credit card details.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- faqs end -->

    <!-- testimonial start -->
    <section class="section bg-light" id="testimonials">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="text-center mb-4">
                        <h3>Customer Reviews</h3>
                        <p class="text-muted">See what our happy customers have to say about their shopping experience with Supply Hub</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4">
                    <div class="testi-box mt-4">
                        <div class="testi-desc bg-white p-4">
                            <div class="text-warning mb-3">
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                            </div>
                            <p class="text-muted mb-0">"Amazing selection of products and super fast delivery! I ordered some electronics and they arrived the next day, perfectly packaged. The customer service team was very helpful when I had questions."</p>
                        </div>
                        <div class="p-4">
                            <div class="testi-img float-start me-2">
                                <img src="assets/images/users/avatar-1.jpg" alt="" class="rounded-circle">
                            </div>
                            <div>
                                <h5 class="mb-0">Sarah Johnson</h5>
                                <p class="text-muted m-0"><small>Premium Member</small></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="testi-box mt-4">
                        <div class="testi-desc bg-white p-4">
                            <div class="text-warning mb-3">
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                            </div>
                            <p class="text-muted mb-0">"The rewards program is fantastic! I've earned so much cashback on my purchases. The prices are competitive and the quality of products is consistently high. Highly recommend!"</p>
                        </div>
                        <div class="p-4">
                            <div class="testi-img float-start me-2">
                                <img src="assets/images/users/avatar-2.jpg" alt="" class="rounded-circle">
                            </div>
                            <div>
                                <h5 class="mb-0">David Chen</h5>
                                <p class="text-muted m-0"><small>Business Member</small></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="testi-box mt-4">
                        <div class="testi-desc bg-white p-4">
                            <div class="text-warning mb-3">
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star-half"></i>
                            </div>
                            <p class="text-muted mb-0">"What I love most about Supply Hub is their commitment to security. The checkout process is smooth and I feel completely safe making purchases. Their return policy is also very customer-friendly."</p>
                        </div>
                        <div class="p-4">
                            <div class="testi-img float-start me-2">
                                <img src="assets/images/users/avatar-3.jpg" alt="" class="rounded-circle">
                            </div>
                            <div>
                                <h5 class="mb-0">Emily Martinez</h5>
                                <p class="text-muted m-0"><small>Verified Buyer</small></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- testimonial end -->

    <!-- contact start -->
    <section class="section pb-0 bg-gradient" id="contact">
        <div class="bg-shape">
            <img src="assets/welcome/images/bg-shape-light.png" alt="" class="img-fluid mx-auto d-block">
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="title text-center mb-4">
                        <h3 class="text-white">Need Help with Your Order?</h3>
                        <p class="text-white-50">Our customer support team is here to help. Contact us through any of these channels:</p>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="contact-content text-center mt-4">
                        <div class="contact-icon mb-2">
                            <i class="mdi mdi-chat-processing text-info h2"></i>
                        </div>
                        <div class="contact-details text-white">
                            <h6 class="text-white">Live Chat</h6>
                            <p class="text-white-50">Available 24/7<br>Average response time: 2 mins</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="contact-content text-center mt-4">
                        <div class="contact-icon mb-2">
                            <i class="mdi mdi-email-outline text-info h2"></i>
                        </div>
                        <div class="contact-details text-white">
                            <h6 class="text-white">Email Support</h6>
                            <p class="text-white-50">support@supplyhub.com<br>Response within 24 hours</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="contact-content text-center mt-4">
                        <div class="contact-icon mb-2">
                            <i class="mdi mdi-phone text-info h2"></i>
                        </div>
                        <div class="contact-details text-white">
                            <h6 class="text-white">Phone Support</h6>
                            <p class="text-white-50">1-800-SUPPLYHUB<br>Mon-Fri: 9AM-6PM EST</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="custom-form p-5 bg-white">
                        <div class="text-center mb-4">
                            <h4>Send Us a Message</h4>
                            <p class="text-muted">Fill out this form and we'll get back to you as soon as possible.</p>
                        </div>
                        <span id="error-msg"></span>
                        <form method="post" name="myForm" onsubmit="return validateForm()">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input name="name" id="name" type="text" class="form-control" placeholder="Enter your full name">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input name="email" id="email" type="email" class="form-control" placeholder="Enter your email">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="orderNumber" class="form-label">Order Number (if applicable)</label>
                                        <input name="orderNumber" id="orderNumber" type="text" class="form-control" placeholder="Enter your order number">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="subject" class="form-label">Subject</label>
                                        <select name="subject" id="subject" class="form-control">
                                            <option value="">Select a topic</option>
                                            <option value="order">Order Status</option>
                                            <option value="return">Returns & Refunds</option>
                                            <option value="product">Product Information</option>
                                            <option value="shipping">Shipping & Delivery</option>
                                            <option value="account">Account Issues</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="message" class="form-label">Message</label>
                                        <textarea name="message" id="message" rows="4" class="form-control" placeholder="How can we help you?"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 text-end">
                                    <button type="submit" class="btn btn-primary">Send Message <i class="mdi mdi-send ms-1"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- contact end -->

    <!-- cta start -->
    <section class="section-sm bg-light">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <h3 class="mb-0 mo-mb-20">We also customize the theme as per your needs</h3>
                </div>
                <div class="col-md-3">
                    <div class="text-md-end">
                        <a href="#" class="btn btn-outline-dark rounded-pill"><i class="mdi mdi-email-outline me-1"></i> Contact Us</a>
                    </div>
                </div>
            </div>
            <!-- end row -->
        </div>
        <!-- end container-fluid -->
    </section>
    <!-- cta end -->

    <!-- footer start -->
    <footer class="bg-dark footer">
        <div class="container-fluid">
            <div class="row mb-5">
                <div class="col-lg-4">
                    <div class="pe-lg-4">
                        <div class="mb-4">
                            <img src="assets/welcome/images/logo-light.png" alt="Supply Hub" height="60" class="rounded-circle">
                        </div>
                        <p class="text-white-50">Your Trusted Online Shopping Destination</p>
                        <p class="text-white-50 mb-4 mb-lg-0">Shop with confidence at Supply Hub. We offer a wide selection of quality products, secure payments, and reliable delivery services to ensure your complete satisfaction.</p>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <div class="footer-list">
                        <p class="text-white mb-2 footer-list-title">Shop</p>
                        <ul class="list-unstyled">
                            <li><a href="customer/index.php"><i class="mdi mdi-chevron-right me-2"></i>Browse Products</a></li>
                            <li><a href="customer/cart.php"><i class="mdi mdi-chevron-right me-2"></i>Shopping Cart</a></li>
                            <li><a href="#categories"><i class="mdi mdi-chevron-right me-2"></i>Categories</a></li>
                            <li><a href="#featured"><i class="mdi mdi-chevron-right me-2"></i>Featured Items</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <div class="footer-list">
                        <p class="text-white mb-2 footer-list-title">My Account</p>
                        <ul class="list-unstyled">
                            <li><a href="auth-login.php"><i class="mdi mdi-chevron-right me-2"></i>Login</a></li>
                            <li><a href="auth-register.php"><i class="mdi mdi-chevron-right me-2"></i>Register</a></li>
                            <li><a href="customer/orders.php"><i class="mdi mdi-chevron-right me-2"></i>My Orders</a></li>
                            <li><a href="customer/checkout.php"><i class="mdi mdi-chevron-right me-2"></i>Checkout</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <div class="footer-list">
                        <p class="text-white mb-2 footer-list-title">Help</p>
                        <ul class="list-unstyled">
                            <li><a href="#faq"><i class="mdi mdi-chevron-right me-2"></i>FAQ</a></li>
                            <li><a href="#shipping"><i class="mdi mdi-chevron-right me-2"></i>Shipping Info</a></li>
                            <li><a href="#contact"><i class="mdi mdi-chevron-right me-2"></i>Contact Us</a></li>
                            <li><a href="#returns"><i class="mdi mdi-chevron-right me-2"></i>Returns Policy</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <div class="footer-list">
                        <p class="text-white mb-2 footer-list-title">Legal</p>
                        <ul class="list-unstyled">
                            <li><a href="#privacy"><i class="mdi mdi-chevron-right me-2"></i>Privacy Policy</a></li>
                            <li><a href="#terms"><i class="mdi mdi-chevron-right me-2"></i>Terms of Service</a></li>
                            <li><a href="#security"><i class="mdi mdi-chevron-right me-2"></i>Security</a></li>
                            <li><a href="#sitemap"><i class="mdi mdi-chevron-right me-2"></i>Sitemap</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- end row -->

            <div class="row">
                <div class="col-lg-12">
                    <div class="float-start pull-none">
                        <p class="text-white-50">&copy; <script>
                                document.write(new Date().getFullYear())
                            </script> Supply Hub. All rights reserved.</p>
                    </div>
                    <div class="float-end pull-none">
                        <ul class="list-inline social-links">
                            <li class="list-inline-item text-white-50">
                                Follow Us:
                            </li>
                            <li class="list-inline-item"><a href="#"><i class="mdi mdi-facebook"></i></a></li>
                            <li class="list-inline-item"><a href="#"><i class="mdi mdi-twitter"></i></a></li>
                            <li class="list-inline-item"><a href="#"><i class="mdi mdi-instagram"></i></a></li>
                            <li class="list-inline-item"><a href="#"><i class="mdi mdi-pinterest"></i></a></li>
                        </ul>
                    </div>
                </div>
                <!-- end col -->
            </div>
            <!-- end row -->

            <!-- Payment Methods -->
            <div class="row mt-4">
                <div class="col-lg-12 text-center">
                    <p class="text-white-50 mb-3">Payment Methods</p>
                    <img src="assets/images/payments/cod.png" alt="amex" height="25" class="mx-1">
                </div>
            </div>
        </div>
        <!-- container-fluid -->
    </footer>
    <!-- footer end -->

    <!-- Back to top -->
    <!-- <a href="#" class="back-to-top" id="back-to-top"> <i class="mdi mdi-chevron-up"> </i> </a> -->
    <!-- Back to top -->
    <a href="#" onclick="topFunction()" class="back-to-top-btn btn btn-primary" id="back-to-top-btn"><i class="mdi mdi-chevron-up"></i></a>

    <!-- javascript -->

    <script src="assets/welcome/js/bootstrap.bundle.min.js"></script>

    <!-- custom js -->
    <script src="assets/welcome/js/app.js"></script>

    <!-- CSS for categories and products -->
    <style>
        .category-card {
            transition: transform 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .category-card:hover {
            transform: translateY(-5px);
        }

        .product-card {
            position: relative;
            transition: transform 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            color: white;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }

        .product-rating {
            font-size: 14px;
        }

        .product-price {
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</body>

</html>