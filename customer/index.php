<?php
include __DIR__ . '/../partials/main.php';

use SLSupplyHub\Product;
use SLSupplyHub\Category;
use SLSupplyHub\Wishlist;

// Initialize models
$productModel = new Product();
$categoryModel = new Category();
$wishlistModel = new Wishlist();

// Get filter parameters
$filters = [
    'search' => $_GET['search'] ?? '',
    'category' => (int)($_GET['category'] ?? 0),
    'min_price' => (float)($_GET['min_price'] ?? 0),
    'max_price' => (float)($_GET['max_price'] ?? 0),
    'rating' => (int)($_GET['rating'] ?? 0),
    'in_stock' => isset($_GET['in_stock']),
    'sort' => $_GET['sort'] ?? 'newest',
    'page' => max(1, (int)($_GET['page'] ?? 1)),
    'per_page' => 12
];

// Get categories for filter
$categories = $categoryModel->getAllCategories();

// Get user's wishlist items
$wishlistItems = [];
if ($session->getUserId()) {
    $wishlistItems = $wishlistModel->getWishlistItems($session->getUserId());
}

// Search products
$products = $productModel->searchProducts($filters);
?>

<head>
    <?php
    $title = "Browsing";
    $sub_title = "Products";
    $page_title = "Browse Products";
    include '../partials/title-meta.php'; ?>
    <?php include '../partials/head-css.php'; ?>
    <link rel="stylesheet" href="<?= asset_url('libs/toastr/build/toastr.min.css') ?>">
</head>

<body>
    <div id="wrapper">
        <?php include 'menu.php'; ?>

        <div class="content-page">
            <?php include 'topbar.php'; ?>

            <div class="content">
                <div class="container-fluid">
                    <?php
                    include '../partials/page-title.php'; ?>

                    <div class="row">
                        <!-- Filters Sidebar -->
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-3">Filters</h4>

                                    <form action="" method="GET" id="filterForm">
                                        <!-- Search -->
                                        <div class="mb-3">
                                            <label class="form-label">Search</label>
                                            <input type="text"
                                                class="form-control"
                                                name="search"
                                                value="<?php echo htmlspecialchars($filters['search']); ?>"
                                                placeholder="Search products...">
                                        </div>

                                        <!-- Categories -->
                                        <div class="mb-3">
                                            <label class="form-label">Category</label>
                                            <select class="form-select" name="category">
                                                <option value="">All Categories</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category['id']; ?>"
                                                        <?php echo $filters['category'] == $category['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Price Range -->
                                        <div class="mb-3">
                                            <label class="form-label">Price Range</label>
                                            <div class="row g-2">
                                                <div class="col">
                                                    <input type="number"
                                                        class="form-control"
                                                        name="min_price"
                                                        placeholder="Min"
                                                        value="<?php echo $filters['min_price'] ?: ''; ?>">
                                                </div>
                                                <div class="col">
                                                    <input type="number"
                                                        class="form-control"
                                                        name="max_price"
                                                        placeholder="Max"
                                                        value="<?php echo $filters['max_price'] ?: ''; ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Rating Filter -->
                                        <div class="mb-3">
                                            <label class="form-label">Minimum Rating</label>
                                            <div class="rating-filter">
                                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                                    <div class="form-check">
                                                        <input type="radio"
                                                            id="rating<?php echo $i; ?>"
                                                            name="rating"
                                                            value="<?php echo $i; ?>"
                                                            class="form-check-input"
                                                            <?php echo $filters['rating'] == $i ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="rating<?php echo $i; ?>">
                                                            <?php for ($j = 1; $j <= 5; $j++): ?>
                                                                <i class="mdi mdi-star<?php echo $j <= $i ? ' text-warning' : '-outline text-muted'; ?>"></i>
                                                            <?php endfor; ?>
                                                            & Up
                                                        </label>
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                        </div>

                                        <!-- Stock Status -->
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input type="checkbox"
                                                    class="form-check-input"
                                                    name="in_stock"
                                                    id="inStock"
                                                    <?php echo $filters['in_stock'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="inStock">In Stock Only</label>
                                            </div>
                                        </div>

                                        <!-- Sort By -->
                                        <div class="mb-3">
                                            <label class="form-label">Sort By</label>
                                            <select class="form-select" name="sort">
                                                <option value="newest" <?php echo $filters['sort'] === 'newest' ? 'selected' : ''; ?>>
                                                    Newest First
                                                </option>
                                                <option value="price_low" <?php echo $filters['sort'] === 'price_low' ? 'selected' : ''; ?>>
                                                    Price: Low to High
                                                </option>
                                                <option value="price_high" <?php echo $filters['sort'] === 'price_high' ? 'selected' : ''; ?>>
                                                    Price: High to Low
                                                </option>
                                                <option value="popular" <?php echo $filters['sort'] === 'popular' ? 'selected' : ''; ?>>
                                                    Most Popular
                                                </option>
                                                <option value="rating" <?php echo $filters['sort'] === 'rating' ? 'selected' : ''; ?>>
                                                    Highest Rated
                                                </option>
                                            </select>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                                        <?php if (array_filter($filters, fn($v) => !empty($v) && $v !== 'newest' && $v !== 1)): ?>
                                            <a href="?" class="btn btn-light w-100 mt-2">Clear Filters</a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>


                        <!-- Products Grid -->
                        <div class="col-lg-9">
                            <div class="row">
                                <?php foreach ($products['items'] as $product): ?>
                                    <div class="col-md-6 col-lg-4 col-xl-4">
                                        <div class="card product-box">
                                            <div class="card-body">
                                                <?php if ($product['discount_percent'] > 0): ?>
                                                    <div class="product-badge bg-danger">
                                                        <?php echo $product['discount_percent']; ?>% OFF
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($product['is_new']): ?>
                                                    <div class="badge bg-soft-success text-success mb-4">New</div>
                                                <?php endif; ?>

                                                <div class="bg-light text-center p-2">
                                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                                        <img src="<?php echo htmlspecialchars(base_url($product['image_path'])); ?>"
                                                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                            class="img-fluid product-img">
                                                    </a>
                                                </div>

                                                <div class="product-action">
                                                    <button type="button"
                                                        class="btn btn-primary add-to-cart"
                                                        data-product-id="<?php echo $product['id']; ?>"
                                                        <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                                                        <i class="mdi mdi-cart"></i>
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-<?php echo in_array($product['id'], $wishlistItems) ? 'danger' : 'light'; ?> add-to-wishlist"
                                                        data-product-id="<?php echo $product['id']; ?>"
                                                        data-in-wishlist="<?php echo in_array($product['id'], $wishlistItems) ? 'true' : 'false'; ?>">
                                                        <i class="mdi <?php echo in_array($product['id'], $wishlistItems) ? 'mdi-heart' : 'mdi-heart-outline'; ?>"></i>                                                    </button>
                                                </div>

                                                <div class="product-info">
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <h5 class="mt-3">
                                                                <a href="product-detail.php?id=<?php echo $product['id']; ?>"
                                                                    class="text-dark">
                                                                    <?php echo htmlspecialchars($product['name']); ?>
                                                                </a>
                                                            </h5>
                                                            <div class="text-warning mb-2">
                                                                <?php
                                                                $rating = $product['rating'];
                                                                for ($i = 1; $i <= 5; $i++) {
                                                                    if ($i <= $rating) {
                                                                        echo '<i class="mdi mdi-star"></i>';
                                                                    } elseif ($i - 0.5 <= $rating) {
                                                                        echo '<i class="mdi mdi-star-half"></i>';
                                                                    } else {
                                                                        echo '<i class="mdi mdi-star-outline"></i>';
                                                                    }
                                                                }
                                                                ?>
                                                                <small class="text-muted ms-1">
                                                                    (<?php echo $product['review_count']; ?> reviews)
                                                                </small>
                                                            </div>
                                                            <h5 class="mt-3 mb-0">
                                                                <?php if ($product['discount_percent'] > 0): ?>
                                                                    <span class="text-danger">
                                                                        $<?php echo number_format($product['discounted_price'], 2); ?>
                                                                    </span>
                                                                    <small class="text-muted text-decoration-line-through ms-1">
                                                                        $<?php echo number_format($product['regular_price'], 2); ?>
                                                                    </small>
                                                                <?php else: ?>
                                                                    spa
                                                                    <span class="text-dark">
                                                                        $<?php echo number_format($product['price'], 2); ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </h5>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Pagination -->
                            <?php if ($products['last_page'] > 1): ?>
                                <nav class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($products['current_page'] > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="?<?php echo http_build_query(array_merge($filters, ['page' => $products['current_page'] - 1])); ?>">
                                                    Previous
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = 1; $i <= $products['last_page']; $i++): ?>
                                            <li class="page-item <?php echo $i === $products['current_page'] ? 'active' : ''; ?>">
                                                <a class="page-link"
                                                    href="?<?php echo http_build_query(array_merge($filters, ['page' => $i])); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($products['current_page'] < $products['last_page']): ?>
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="?<?php echo http_build_query(array_merge($filters, ['page' => $products['current_page'] + 1])); ?>">
                                                    Next
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?> 
                        </div>
                    </div>
                </div>
            </div>

            <?php include '../partials/footer.php'; ?>
        </div>
    </div>

    <?php include '../partials/right-sidebar.php'; ?>
    <?php include '../partials/footer-scripts.php'; ?>
    <script src="<?= asset_url('libs/toastr/build/toastr.min.css') ?>"></script>

    <script>
        $(document).ready(function() {
            // Add to cart functionality
            $('.add-to-cart').click(function() {
                var btn = $(this);
                var productId = btn.data('product-id');

                $.ajax({
                    url: 'ajax/cart.php',
                    type: 'POST',
                    data: {
                        action: 'add',
                        product_id: productId,
                        quantity: 1
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.success) {
                            $('.cart-count').text(data.cart_count);
                            toastr.success('Product added to cart successfully!');

                        } else {
                            toastr.error(data.message);
                        }
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
                    data: {
                        action: inWishlist ? 'remove' : 'add',
                        product_id: productId
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.success) {
                            btn.data('in-wishlist', !inWishlist);
                            btn.toggleClass('btn-danger btn-light');
                            btn.find('i').toggleClass('mdi-heart mdi-heart-outline');
                            btn.find('span').text(inWishlist ? 'Add to Wishlist' : 'Remove from Wishlist');

                            $('.wishlist-count').text(data.wishlist_count);

                            toastr.success(inWishlist ? 'Product removed from wishlist!' : 'Product added to wishlist!');
                        } else {
                            toastr.error(data.message);
                        }
                    }
                });
            });

            // Auto-submit filter form when sort or rating changes
            $('select[name="sort"], input[name="rating"]').change(function() {
                $('#filterForm').submit();
            });
        });
    </script>
</body>

</html>