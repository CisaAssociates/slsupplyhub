<?php
include '../partials/main.php';
use SLSupplyHub\Product;
use SLSupplyHub\Feedback;

// Initialize models
$productModel = new Product();
$feedbackModel = new Feedback();

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get product details
$product = $productModel->find($productId);
if (!$product) {
    header('Location: index.php');
    exit;
}

// Add image path if not absolute
if ($product['image_path'] && !filter_var($product['image_path'], FILTER_VALIDATE_URL)) {
    $product['image_path'] = '../' . ltrim($product['image_path'], '/');
}

// Get feedback summary
$reviewSummary = $feedbackModel->getFeedbackSummary('product', $productId);

// Get product reviews with pagination
$page = isset($_GET['review_page']) ? (int)$_GET['review_page'] : 1;
$reviewsPerPage = 5;
$reviews = $feedbackModel->getProductReviews($productId, $page, $reviewsPerPage);

// Check if user has purchased the product
$canReview = false;
$hasReviewed = false;
if ($session->getUserId()) {
    $canReview = $productModel->hasUserPurchased($session->getUserId(), $productId);
    $hasReviewed = $feedbackModel->hasUserReviewed($session->getUserId(), $productId);
}

// Near the top of the file, before line 569
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>

<head>
    <?php
    $title = htmlspecialchars($product['name']);
    $sub_title = "Product Details";
    $page_title = "Product Details";
    include '../partials/title-meta.php'; ?>
    <?php include '../partials/head-css.php'; ?>
    <link href="<?= asset_url('libs/selectize/css/selectize.bootstrap3.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= asset_url('libs/sweetalert2/sweetalert2.min.css') ?>" rel="stylesheet" type="text/css" />
</head>

<body>
    <div id="wrapper">
        <?php include 'menu.php'; ?>
        <div class="content-page">
            <?php include '../partials/topbar.php'; ?>
            
            <div class="content">
                <div class="container-fluid">
                    <?php include '../partials/page-title.php'; ?>
                    <!-- Product Details Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-5">
                                            <!-- Product Images -->
                                            <div class="tab-content pt-0">
                                                <?php if (!empty($product['image_path'])): ?>
                                                <div class="tab-pane active show" id="product-1-item">
                                                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                         class="img-fluid mx-auto d-block rounded">
                                                </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($images)): ?>
                                                    <?php foreach ($images as $index => $image): ?>
                                                        <div class="tab-pane <?php echo empty($product['image_path']) && $index === 0 ? 'active show' : ''; ?>" 
                                                             id="product-<?php echo $index + 2; ?>-item">
                                                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                                                 alt="" class="img-fluid mx-auto d-block rounded">
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>

                                            <ul class="nav nav-pills nav-justified">
                                                <?php if (!empty($product['image_path'])): ?>
                                                    <li class="nav-item">
                                                        <a href="#product-1-item" 
                                                           data-bs-toggle="tab" 
                                                           aria-expanded="false" 
                                                           class="nav-link product-thumb <?php echo empty($product['image_path']) && $index === 0 ? 'active show' : ''; ?>">
                                                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                                                 alt="" class="img-fluid mx-auto d-block rounded">
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($images)): ?>
                                                    <?php foreach ($images as $index => $image): ?>
                                                        <li class="nav-item">
                                                            <a href="#product-<?php echo $index + 2; ?>-item" 
                                                               data-bs-toggle="tab" 
                                                               aria-expanded="false" 
                                                               class="nav-link product-thumb <?php echo empty($product['image_path']) && $index === 0 ? 'active show' : ''; ?>">
                                                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                                                     alt="" class="img-fluid mx-auto d-block rounded">
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </ul>
                                        </div>

                                        <div class="col-lg-7">
                                            <div class="ps-xl-3 mt-3 mt-xl-0">
                                                <h4 class="mb-3"><?php echo htmlspecialchars($product['name']); ?></h4>
                                                
                                                <div class="mb-3">
                                                    <div class="text-warning mb-1">
                                                        <?php 
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            if ($i <= $product['rating']) {
                                                                echo '<i class="mdi mdi-star"></i>';
                                                            } elseif ($i - 0.5 <= $product['rating']) {
                                                                echo '<i class="mdi mdi-star-half"></i>';
                                                            } else {
                                                                echo '<i class="mdi mdi-star-outline"></i>';
                                                            }
                                                        }
                                                        ?>
                                                        <span class="text-muted ms-2">
                                                            (<?php echo number_format($product['rating'], 1); ?> / 5.0)
                                                        </span>
                                                        <span class="text-muted ms-2">
                                                            <?php echo $product['review_count']; ?> reviews
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="mt-3">
                                                    <?php if ($product['discount_percent'] > 0): ?>
                                                        <h3 class="text-danger mb-0">
                                                            $<?php echo number_format($product['discounted_price'], 2); ?>
                                                            <small class="text-muted text-decoration-line-through ms-2">
                                                                $<?php echo number_format($product['regular_price'], 2); ?>
                                                            </small>
                                                            <small class="text-danger ms-2">
                                                                (<?php echo $product['discount_percent']; ?>% off)
                                                            </small>
                                                        </h3>
                                                    <?php else: ?>
                                                        <h3>$<?php echo number_format($product['price'], 2); ?></h3>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="mt-4">
                                                    <h6 class="font-14">Description:</h6>
                                                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                                                </div>

                                                <form class="mt-4">
                                                    <div class="row mb-3">
                                                        <div class="col-4">
                                                            <div class="input-group">
                                                                <span class="input-group-text">Qty</span>
                                                                <input type="number" class="form-control" id="product-quantity" 
                                                                       min="1" max="<?php echo $product['stock']; ?>" value="1">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" 
                                                            class="btn btn-primary me-2 add-to-cart"
                                                            data-product-id="<?php echo $product['id']; ?>"
                                                            <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                                                        <i class="mdi mdi-cart me-1"></i> 
                                                        <?php echo $product['stock'] == 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-<?php echo in_array($product['id'], $wishlistItems ?? []) ? 'danger' : 'light'; ?> add-to-wishlist"
                                                            data-product-id="<?php echo $product['id']; ?>"
                                                            data-in-wishlist="<?php echo in_array($product['id'], $wishlistItems ?? []) ? 'true' : 'false'; ?>">
                                                        <i class="mdi <?php echo in_array($product['id'], $wishlistItems ?? []) ? 'mdi-heart' : 'mdi-heart-outline'; ?>"></i>
                                                    </button>
                                                </form>

                                                <div class="mt-4">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6 class="font-14">Available Stock:</h6>
                                                            <p class="text-sm lh-150">
                                                                <?php if ($product['stock'] < 10): ?>
                                                                    <span class="text-danger">Only <?php echo $product['stock']; ?> left</span>
                                                                <?php else: ?>
                                                                    <?php echo $product['stock']; ?> units
                                                                <?php endif; ?>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6 class="font-14">Sold By:</h6>
                                                            <p class="text-sm lh-150"><?php echo htmlspecialchars($product['supplier_name']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="review-summary text-center">
                                                <h2 class="display-4 mb-2"><?php echo number_format($product['rating'], 1); ?></h2>
                                                <div class="text-warning mb-3">
                                                    <?php 
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $product['rating']) {
                                                            echo '<i class="mdi mdi-star"></i>';
                                                        } elseif ($i - 0.5 <= $product['rating']) {
                                                            echo '<i class="mdi mdi-star-half"></i>';
                                                        } else {
                                                            echo '<i class="mdi mdi-star-outline"></i>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <p class="text-muted mb-0">Based on <?php echo $product['review_count']; ?> reviews</p>
                                            </div>

                                            <div class="rating-distribution mt-4">
                                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <div class="text-nowrap me-3">
                                                            <span class="text-warning"><i class="mdi mdi-star"></i></span>
                                                            <span class="ms-1"><?php echo $i; ?></span>
                                                        </div>
                                                        <div class="progress w-100" style="height: 6px;">
                                                            <?php
                                                            $percentage = $reviewSummary['rating_distribution'][$i] ?? 0;
                                                            ?>
                                                            <div class="progress-bar bg-warning" role="progressbar" 
                                                                 style="width: <?php echo $percentage; ?>%" 
                                                                 aria-valuenow="<?php echo $percentage; ?>" 
                                                                 aria-valuemin="0" 
                                                                 aria-valuemax="100"></div>
                                                        </div>
                                                        <div class="text-muted ms-3" style="min-width: 40px;">
                                                            <?php echo number_format($percentage, 0); ?>%
                                                        </div>
                                                    </div>
                                                <?php endfor; ?>
                                            </div>

                                            <?php if ($canReview && !$hasReviewed): ?>
                                                <div class="mt-4">
                                                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                                        Write a Review
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-lg-8">
                                            <div class="review-list">
                                                <?php if (empty($reviews['items'])): ?>
                                                    <div class="text-center py-4">
                                                        <p class="text-muted mb-0">No reviews yet. Be the first to review this product!</p>
                                                    </div>
                                                <?php else: ?>
                                                    <?php foreach ($reviews['items'] as $review): ?>
                                                        <div class="review-item border-bottom pb-3 mb-3">
                                                            <div class="d-flex align-items-start">
                                                                <div class="flex-shrink-0">
                                                                    <div class="avatar-sm">
                                                                        <span class="avatar-title bg-primary-subtle text-primary rounded">
                                                                            <?php echo strtoupper(substr($review['customer_name'], 0, 1)); ?>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-grow-1 ms-3">
                                                                    <h5 class="mt-0 mb-1"><?php echo htmlspecialchars($review['customer_name']); ?></h5>
                                                                    <div class="text-warning mb-2">
                                                                        <?php 
                                                                        for ($i = 1; $i <= 5; $i++) {
                                                                            echo '<i class="mdi mdi-star' . ($i <= $review['rating'] ? '' : '-outline') . '"></i>';
                                                                        }
                                                                        ?>
                                                                        <span class="text-muted ms-2">
                                                                            <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                                                        </span>
                                                                    </div>
                                                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>

                                                    <!-- Pagination -->
                                                    <?php if ($reviews['last_page'] > 1): ?>
                                                        <nav class="mt-4">
                                                            <ul class="pagination justify-content-center">
                                                                <?php if ($reviews['current_page'] > 1): ?>
                                                                    <li class="page-item">
                                                                        <a class="page-link" href="?id=<?php echo $productId; ?>&review_page=<?php echo $reviews['current_page'] - 1; ?>">
                                                                            Previous
                                                                        </a>
                                                                    </li>
                                                                <?php endif; ?>

                                                                <?php for ($i = 1; $i <= $reviews['last_page']; $i++): ?>
                                                                    <li class="page-item <?php echo $i === $reviews['current_page'] ? 'active' : ''; ?>">
                                                                        <a class="page-link" href="?id=<?php echo $productId; ?>&review_page=<?php echo $i; ?>">
                                                                            <?php echo $i; ?>
                                                                        </a>
                                                                    </li>
                                                                <?php endfor; ?>

                                                                <?php if ($reviews['current_page'] < $reviews['last_page']): ?>
                                                                    <li class="page-item">
                                                                        <a class="page-link" href="?id=<?php echo $productId; ?>&review_page=<?php echo $reviews['current_page'] + 1; ?>">
                                                                            Next
                                                                        </a>
                                                                    </li>
                                                                <?php endif; ?>
                                                            </ul>
                                                        </nav>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product reviews section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Reviews</h4>
                                    <div id="productReviews"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <?php include '../partials/footer.php'; ?>
        </div>
    </div>

    <?php include '../partials/right-sidebar.php'; ?>
    <?php include '../partials/footer-scripts.php'; ?>

    <script src="<?= asset_url('libs/selectize/js/standalone/selectize.min.js') ?>"></script>
    <script src="<?= asset_url('libs/sweetalert2/sweetalert2.min.js') ?>"></script>

    <!-- Review Modal -->
    <?php if ($canReview && !$hasReviewed): ?>
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Write a Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="reviewForm">
                        <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                        
                        <div class="mb-3 text-center">
                            <div class="rate">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" />
                                    <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> stars">
                                        <i class="mdi mdi-star"></i>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="review-comment" class="form-label">Your Review</label>
                            <textarea class="form-control" id="review-comment" name="comment" rows="4" required></textarea>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="submitReview">Submit Review</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>



    <style>
    .rate {
        display: inline-block;
        border: 0;
        direction: rtl;
    }
    .rate input {
        display: none;
    }
    .rate label {
        float: right;
        color: #ddd;
        font-size: 24px;
        padding: 0 2px;
        cursor: pointer;
    }
    .rate label:hover,
    .rate label:hover ~ label,
    .rate input:checked ~ label {
        color: #f1b44c;
    }
    </style>

    <script>
    $(document).ready(function() {
        // Add to cart functionality
        $('.add-to-cart').click(function() {
            var productId = $(this).data('product-id');
            var quantity = $('#product-quantity').val();
            
            $.ajax({
                url: 'ajax/cart.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'add',
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {
                        $('.cart-count').text(response.cart_count);
                        // Show success message with SweetAlert2
                        Swal.fire({
                            icon: 'success',
                            title: 'Added to Cart!',
                            text: 'Product has been added to your cart.',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.message || 'Failed to add product to cart'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Failed to add product to cart. Please try again.'
                    });
                    console.error("AJAX Error:", status, error);
                }
            });
        });

        // Wishlist functionality
        $('.add-to-wishlist').click(function() {
            var btn = $(this);
            var productId = btn.data('product-id');
            var inWishlist = btn.data('in-wishlist') === 'true';
            
            $.ajax({
                url: 'ajax/wishlist.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: inWishlist ? 'remove' : 'add',
                    product_id: productId
                },
                success: function(response) {
                    if (response.success) {
                        btn.data('in-wishlist', !inWishlist);
                        btn.toggleClass('btn-danger btn-light');
                        btn.find('i').toggleClass('mdi-heart mdi-heart-outline');
                        
                        $('.wishlist-count').text(response.wishlist_count);
                        
                        Swal.fire({
                            icon: 'success',
                            title: inWishlist ? 'Removed from Wishlist!' : 'Added to Wishlist!',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.message || 'Operation failed'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Operation failed. Please try again.'
                    });
                    console.error("AJAX Error:", status, error);
                }
            });
        });
    });
    </script>

    <!-- Initialize review system -->
    <script src="<?php echo asset_url(); ?>js/reviews.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const reviewManager = new ReviewManager(
            'productReviews', 
            'product',
            '<?php echo htmlspecialchars($productId); ?>'
        );

        // Handle review form submission
        $('#reviewForm').on('submit', function(e) {
            e.preventDefault();
            reviewManager.submitReview(new FormData(this));
        });
    });
    </script>
</body>
</html>