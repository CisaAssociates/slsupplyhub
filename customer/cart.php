<?php
include '../partials/main.php';

use SLSupplyHub\Cart;

// Initialize cart model
$cartModel = new Cart();

// Get cart items
$cart = $cartModel->getCartItems($session->getUserId());
?>

<head>
    <?php
    $title = "Cart";
    $page_title = "Shopping Cart";
    include '../partials/title-meta.php'; ?>
    <?php include '../partials/head-css.php'; ?>
    <link rel="stylesheet" href="<?= asset_url('libs/sweetalert2/sweetalert2.min.css') ?>">
</head>

<body>
    <div id="wrapper">
        <?php include 'menu.php'; ?>
        <div class="content-page">
            <?php include 'topbar.php'; ?>

            <div class="content">
                <div class="container-fluid">
                    <?php include '../partials/page-title.php'; ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-8">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h4 class="header-title mb-0">Shopping Cart (<span class="cart-items-count"><?php echo $cart['count']; ?></span> items)</h4>
                                                <?php if (!empty($cart['items'])): ?>
                                                    <button type="button" class="btn btn-danger clear-cart">
                                                        <i class="mdi mdi-delete me-1"></i> Clear Cart
                                                    </button>
                                                <?php endif; ?>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-borderless table-nowrap table-centered mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Product</th>
                                                            <th>Price</th>
                                                            <th>Quantity</th>
                                                            <th>Total</th>
                                                            <th style="width: 50px;"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="cart-items">
                                                        <?php if (empty($cart['items'])): ?>
                                                            <tr id="empty-cart-row">
                                                                <td colspan="5" class="text-center">
                                                                    Your cart is empty
                                                                </td>
                                                            </tr>
                                                        <?php else: ?>
                                                            <?php foreach ($cart['items'] as $item): ?>
                                                                <tr id="cart-row-<?php echo $item['product_id']; ?>" data-price="<?php echo $item['price']; ?>">
                                                                    <td>
                                                                        <img src="<?php echo '../' . ltrim($item['image_path'], '/'); ?>"
                                                                            alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                                            title="product-img"
                                                                            class="rounded me-3"
                                                                            height="48" />
                                                                        <p class="m-0 d-inline-block align-middle font-16">
                                                                            <a href="product-detail.php?id=<?php echo $item['product_id']; ?>"
                                                                                class="text-reset">
                                                                                <?php echo htmlspecialchars($item['name']); ?>
                                                                            </a>
                                                                        </p>
                                                                    </td>
                                                                    <td>
                                                                        <?php if ($item['discount_percent'] > 0): ?>
                                                                            <span class="text-danger">
                                                                                ₱<?php echo number_format($item['price'], 2); ?>
                                                                            </span>
                                                                            <small class="text-muted text-decoration-line-through d-block">
                                                                                ₱<?php echo number_format($item['regular_price'], 2); ?>
                                                                            </small>
                                                                        <?php else: ?>
                                                                            ₱<?php echo number_format($item['price'], 2); ?>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <input type="number"
                                                                            min="1"
                                                                            max="<?php echo $item['stock']; ?>"
                                                                            value="<?php echo $item['quantity']; ?>"
                                                                            class="form-control cart-quantity"
                                                                            data-product-id="<?php echo $item['product_id']; ?>"
                                                                            style="width: 90px;">
                                                                    </td>
                                                                    <td class="item-subtotal">
                                                                        ₱<?php echo number_format($item['subtotal'], 2); ?>
                                                                    </td>
                                                                    <td>
                                                                        <a href="javascript:void(0);"
                                                                            class="action-icon remove-from-cart"
                                                                            data-product-id="<?php echo $item['product_id']; ?>">
                                                                            <i class="mdi mdi-delete"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="row mt-4">
                                                <div class="col-sm-6">
                                                    <a href="index.php" class="btn text-muted d-none d-sm-inline-block btn-link fw-semibold">
                                                        <i class="mdi mdi-arrow-left"></i> Continue Shopping
                                                    </a>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="text-sm-end">
                                                        <?php if (!empty($cart['items'])): ?>
                                                            <a href="checkout.php" class="btn btn-danger">
                                                                <i class="mdi mdi-cart-plus me-1"></i> Checkout
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="border p-3 mt-4 mt-lg-0 rounded">
                                                <h4 class="header-title mb-3">Order Summary</h4>

                                                <div class="table-responsive">
                                                    <table class="table mb-0">
                                                        <tbody>
                                                            <tr>
                                                                <td>Grand Total :</td>
                                                                <td class="cart-subtotal">₱<?php echo number_format($cart['total'], 2); ?></td>
                                                            </tr>
                                                            <?php if ($cart['total'] > 0): ?>
                                                                <tr>
                                                                    <td>Shipping Charge :</td>
                                                                    <td class="shipping-cost">₱<?php echo number_format($cart['shipping'], 2); ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Estimated Tax (5%) :</td>
                                                                    <td class="tax-amount">₱<?php echo number_format($cart['total'] * 0.05, 2); ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Total :</th>
                                                                    <th class="cart-total">₱<?php
                                                                                            echo number_format($cart['total'] + $cart['shipping'] + ($cart['total'] * 0.05), 2);
                                                                                            ?></th>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <?php if ($cart['total'] > 0): ?>
                                                <div class="alert alert-warning mt-3" role="alert">
                                                    Free shipping on orders over ₱3,000!
                                                </div>

                                                <div class="input-group mt-3">
                                                    <input type="text" class="form-control" id="coupon-code" placeholder="Enter coupon code">
                                                    <button class="btn btn-primary" type="button" id="apply-coupon">Apply</button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
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
    <script src="<?= asset_url('libs/sweetalert2/sweetalert2.min.js') ?>"></script>

    <script>
        $(document).ready(function() {
            // Handle quantity updates
            $('.cart-quantity').change(function() {
                var $input = $(this);
                var quantity = parseInt($input.val());
                var productId = $input.data('product-id');
                var $row = $('#cart-row-' + productId);
                var price = parseFloat($row.data('price'));

                // Update item subtotal immediately for better UX
                var subtotal = price * quantity;
                $row.find('.item-subtotal').text('₱' + formatNumber(subtotal));

                updateCart(productId, quantity);
            });

            // Handle item removal
            $('.remove-from-cart').click(function() {
                var productId = $(this).data('product-id');
                var $row = $('#cart-row-' + productId);

                $.ajax({
                    url: 'ajax/cart.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'remove',
                        product_id: productId
                    },
                    success: function(data) {
                        if (data.success) {
                            $row.fadeOut(300, function() {
                                $(this).remove();

                                // Update cart count in header
                                $('.cart-count').text(data.cart_count);
                                $('.cart-items-count').text(data.cart_count);

                                // Update cart totals
                                updateCartDisplayFromResponse(data);

                                // Show empty cart message if no items left
                                if (data.cart_count === 0) {
                                    $('#cart-items').html('<tr id="empty-cart-row"><td colspan="5" class="text-center">Your cart is empty</td></tr>');
                                    $('.clear-cart').hide();
                                }
                            });
                        } else {
                            showError(data.message);
                        }
                    },
                    error: function() {
                        showError('An error occurred while updating the cart');
                    }
                });
            });

            // Handle clear cart
            $('.clear-cart').click(function() {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, clear it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'ajax/cart.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'clear'
                            },
                            success: function(data) {
                                if (data.success) {
                                    $('#cart-items').html('<tr id="empty-cart-row"><td colspan="5" class="text-center">Your cart is empty</td></tr>');
                                    $('.cart-count').text('0');
                                    $('.cart-items-count').text('0');
                                    $('.clear-cart').hide();
                                    updateCartDisplayFromResponse(data);
                                } else {
                                    showError(data.message);
                                }
                            },
                            error: function() {
                                showError('An error occurred while clearing the cart');
                            }
                        });
                    }
                });
            });

            function updateCart(productId, quantity) {
                $.ajax({
                    url: 'ajax/cart.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'update',
                        product_id: productId,
                        quantity: quantity
                    },
                    success: function(data) {
                        if (data.success) {
                            // Update cart counts
                            $('.cart-count').text(data.cart_count);
                            $('.cart-items-count').text(data.cart_count);

                            // Update cart totals
                            updateCartDisplayFromResponse(data);
                        } else {
                            showError(data.message);
                        }
                    },
                    error: function() {
                        showError('An error occurred while updating the cart');
                    }
                });
            }

            function updateCartDisplayFromResponse(data) {
                $('.cart-subtotal').text('₱' + data.cart_subtotal);
                $('.shipping-cost').text('₱' + data.cart_shipping);
                $('.tax-amount').text('₱' + data.cart_tax);
                $('.cart-total').text('₱' + data.cart_total);

                // Hide the order summary if cart is empty
                if (data.cart_count === 0) {
                    $('.border.p-3.mt-4').hide();
                    $('.alert-warning').hide();
                }
            }

            function showError(message) {
                $.NotificationApp.send(
                    "Error",
                    message,
                    "top-right",
                    "rgba(0,0,0,0.2)",
                    "error"
                );
            }

            function formatNumber(number) {
                return number.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            }

            // Handle coupon application
            $('#apply-coupon').click(function() {
                var code = $('#coupon-code').val();
                if (!code) {
                    showError("Please enter a coupon code");
                    return;
                }

                $.ajax({
                    url: 'ajax/apply-coupon.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        code: code
                    },
                    success: function(data) {
                        if (data.success) {
                            location.reload();
                        } else {
                            showError(data.message);
                        }
                    },
                    error: function() {
                        showError('An error occurred while applying the coupon');
                    }
                });
            });
        });
    </script>
</body>

</html>