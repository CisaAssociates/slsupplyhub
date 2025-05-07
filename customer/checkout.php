<?php
require_once '../partials/main.php';

use SLSupplyHub\Cart;
use SLSupplyHub\Address;

// Initialize models
$cartModel = new Cart();
$addressModel = new Address();

// Get cart items
$cart = $cartModel->getCartItems($session->getUserId());

// Redirect if cart is empty
if (empty($cart['items'])) {
    header('Location: cart.php');
    exit;
}

// Get saved addresses
$addresses = $addressModel->getUserAddresses($session->getUserId());
$defaultAddress = $addressModel->getDefaultAddress($session->getUserId());
?>

<head>
    <?php
    $title = "Checkout";
    $page_title = "Checkout";
    include '../partials/title-meta.php';
    include '../partials/head-css.php';
    ?>
    <link href="<?= asset_url('libs/sweetalert2/sweetalert2.min.css') ?>" rel="stylesheet" type="text/css" />
</head>

<body>
    <div id="wrapper">
        <?php include 'menu.php'; ?>
        <div class="content-page">
            <?php include 'topbar.php'; ?>

            <div class="content">
                <div class="container-fluid">
                    <?php include '../partials/page-title.php'; ?>

                    <form id="checkout-form">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="mb-4">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h4 class="m-0">Select Delivery Address</h4>
                                                <a href="address.php" class="btn btn-primary btn-sm">
                                                    <i class="mdi mdi-plus"></i> Add New Address
                                                </a>
                                            </div>

                                            <?php if (!empty($addresses)): ?>
                                                <select class="form-control" id="saved_addresses" name="saved_addresses" required>
                                                    <option value="">Select a delivery address</option>
                                                    <?php foreach ($addresses as $address): ?>
                                                        <option value="<?= $address['id'] ?>"
                                                            data-address='<?= json_encode($address) ?>'
                                                            <?= $address['is_default'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($address['first_name'] . ' ' . $address['last_name']) ?> -
                                                            <?= htmlspecialchars($address['street'] . ', ' . $address['barangay'] . ', ' . $address['city']) ?>
                                                            <?= $address['is_default'] ? ' (Default)' : '' ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div id="selected-address-details" class="mt-3">
                                                    <div class="card border">
                                                        <div class="card-body">
                                                            <h5 class="card-title" id="address-name"></h5>
                                                            <p class="card-text mb-1" id="address-street"></p>
                                                            <p class="card-text mb-1" id="address-city"></p>
                                                            <p class="card-text mb-1" id="address-phone"></p>
                                                            <p class="card-text" id="address-email"></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-info mb-0">
                                                    <i class="mdi mdi-information-outline me-1"></i>
                                                    You don't have any saved addresses. Please add a new address to continue with checkout.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-4">
                                            <h4 class="mb-3">Payment Method</h4>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                                                <label class="form-check-label" for="cod">
                                                    Cash on Delivery (COD)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title mb-3">Order Summary</h4>

                                        <div class="table-responsive">
                                            <table class="table mb-0">
                                                <tbody>
                                                    <?php foreach ($cart['items'] as $item): ?>
                                                        <tr>
                                                            <td>
                                                                <img src="<?= '../' . ltrim($item['image_path'], '/') ?>"
                                                                    alt="<?= htmlspecialchars($item['name']) ?>"
                                                                    class="rounded me-2"
                                                                    height="48">
                                                                <p class="d-inline-block align-middle mb-0">
                                                                    <a href="product-detail.php?id=<?= $item['product_id'] ?>"
                                                                        class="text-body fw-semibold">
                                                                        <?= htmlspecialchars($item['name']) ?>
                                                                    </a>
                                                                    <br>
                                                                    <small><?= $item['quantity'] ?> x ₱<?= number_format($item['price'], 2) ?></small>
                                                                </p>
                                                            </td>
                                                            <td class="text-end">₱<?= number_format($item['subtotal'], 2) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    <tr>
                                                        <td>Subtotal:</td>
                                                        <td class="text-end">₱<?= number_format($cart['total'], 2) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Shipping:</td>
                                                        <td class="text-end">₱<?= number_format($cart['shipping'], 2) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Tax (5%):</td>
                                                        <td class="text-end">₱<?= number_format($cart['total'] * 0.05, 2) ?></td>
                                                    </tr>
                                                    <tr class="table-active">
                                                        <th>Total:</th>
                                                        <th class="text-end">₱<?= number_format($cart['total'] + $cart['shipping'] + ($cart['total'] * 0.05), 2) ?></th>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="row mt-4">
                                            <div class="col-sm-6">
                                                <a href="cart.php" class="btn btn-secondary">
                                                    <i class="mdi mdi-arrow-left"></i> Back to Cart
                                                </a>
                                            </div>
                                            <div class="col-sm-6 text-end">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="mdi mdi-cash-multiple me-1"></i> Place Order
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php include '../partials/footer.php'; ?>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <style>
        #loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
    </style>

    <?php include '../partials/right-sidebar.php'; ?>
    <?php include '../partials/footer-scripts.php'; ?>

    <script src="<?= asset_url('libs/sweetalert2/sweetalert2.min.js') ?>"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show selected address details
            function updateAddressDetails(addressData) {
                const details = $('#selected-address-details');
                if (addressData) {
                    $('#address-name').text(addressData.first_name + ' ' + addressData.last_name);
                    $('#address-street').text(addressData.street);
                    $('#address-city').text(addressData.barangay + ', ' + addressData.city + ' ' + addressData.postal_code);
                    $('#address-phone').text('Phone: ' + addressData.phone);
                    $('#address-email').text('Email: ' + addressData.email);
                    details.show();
                } else {
                    details.hide();
                }
            }

            // Handle saved address selection
            $('#saved_addresses').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const addressData = selectedOption.val() ? selectedOption.data('address') : null;
                updateAddressDetails(addressData);
            });

            // Show default address if selected
            const defaultOption = $('#saved_addresses option:selected');
            if (defaultOption.val()) {
                updateAddressDetails(defaultOption.data('address'));
            }

            // Form Submission
            $('#checkout-form').on('submit', function(e) {
                e.preventDefault();

                if (!$('#saved_addresses').val()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Address Required',
                        text: 'Please select a delivery address to continue.'
                    });
                    return;
                }

                $('#loading-overlay').show();
                const formData = $(this).serialize();

                $.ajax({
                    url: 'ajax/process-checkout.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Order Placed Successfully!',
                                text: 'Your order has been placed.',
                                confirmButtonText: 'View Order'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 'order-detail.php?id=' + response.order_id;
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Order Failed',
                                text: response.message || 'Failed to place order. Please try again.'
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Order Failed',
                            text: 'An error occurred while processing your order. Please try again.'
                        });
                    },
                    complete: function() {
                        $('#loading-overlay').hide();
                    }
                });
            });
        });
    </script>
</body>

</html>