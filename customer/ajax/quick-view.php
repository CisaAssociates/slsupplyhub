<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use SLSupplyHub\Product;

// Check if ID is provided
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo 'Product ID is required';
    exit;
}

$productModel = new Product();
$product = $productModel->find($_GET['id']);

if (!$product) {
    http_response_code(404);
    echo 'Product not found';
    exit;
}
?>

<div class="row">
    <div class="col-md-6">
        <div class="bg-light text-center p-4">
            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                 class="img-fluid">
        </div>
    </div>
    <div class="col-md-6">
        <h4 class="mt-0 mb-2"><?php echo htmlspecialchars($product['name']); ?></h4>
        <p class="text-muted mb-4"><?php echo htmlspecialchars($product['category']); ?></p>
        
        <div class="mb-3">
            <div class="text-warning mb-2">
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
                <span class="text-muted ms-2">(<?php echo $product['review_count']; ?> reviews)</span>
            </div>
        </div>

        <div class="mb-3">
            <?php if ($product['discount_percent'] > 0): ?>
                <h3 class="text-danger mb-0">$<?php echo number_format($product['discounted_price'], 2); ?>
                    <small class="text-muted text-decoration-line-through ms-2">
                        $<?php echo number_format($product['price'], 2); ?>
                    </small>
                </h3>
            <?php else: ?>
                <h3 class="mb-0">$<?php echo number_format($product['price'], 2); ?></h3>
            <?php endif; ?>
        </div>

        <p class="mb-4"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

        <div class="mb-3">
            <label class="mb-2">Quantity</label>
            <div class="d-flex">
                <input type="number" class="form-control me-2" style="width: 100px" value="1" min="1" max="<?php echo $product['stock']; ?>" id="quick-view-quantity">
                <button type="button" 
                        class="btn btn-primary add-to-cart"
                        data-product-id="<?php echo $product['id']; ?>"
                        <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                    <i class="mdi mdi-cart me-1"></i> Add to Cart
                </button>
            </div>
        </div>

        <div class="mt-3">
            <p class="mb-2">
                <i class="mdi mdi-truck-delivery text-muted me-1"></i> Free delivery on orders over $50
            </p>
            <p class="mb-0">
                <?php if ($product['stock'] < 10): ?>
                    <i class="mdi mdi-alert text-danger me-1"></i> 
                    <span class="text-danger">Only <?php echo $product['stock']; ?> left</span>
                <?php else: ?>
                    <i class="mdi mdi-check-circle text-success me-1"></i> In stock
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<script>
// Initialize quick view add to cart functionality
$('#quickViewModal .add-to-cart').click(function() {
    var productId = $(this).data('product-id');
    var quantity = $('#quick-view-quantity').val();
    
    $.ajax({
        url: 'ajax/cart.php',
        type: 'POST',
        data: {
            action: 'add',
            product_id: productId,
            quantity: quantity
        },
        success: function(response) {
            var data = JSON.parse(response);
            if (data.success) {
                $('.cart-count').text(data.cart_count);
                $.NotificationApp.send(
                    "Success",
                    "Product added to cart successfully!",
                    "top-right",
                    "rgba(0,0,0,0.2)",
                    "success"
                );
                $('#quickViewModal').modal('hide');
            } else {
                $.NotificationApp.send(
                    "Error",
                    data.message,
                    "top-right",
                    "rgba(0,0,0,0.2)",
                    "error"
                );
            }
        }
    });
});
</script>