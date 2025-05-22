<?php
include '../partials/main.php';

// Include supplier approval check
include 'check-approval.php';

$productService = new SLSupplyHub\Product();
$categoryService = new SLSupplyHub\Category();

// Get product ID from URL if editing
$productId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$product = null;
if ($productId) {
    $product = $productService->find($productId);
    // Verify product belongs to this supplier
    if (!$product || $product['supplier_id'] != $supplierId) {
        header('Location: products.php');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productData = [
        'supplier_id' => $supplierId,
        'name' => $_POST['product_name'],
        'description' => $_POST['product_description'],
        'price' => $_POST['product_price'],
        'regular_price' => $_POST['regular_price'],
        'stock' => $_POST['stock'],
        'category_id' => $_POST['category_id'],
        'unit' => $_POST['unit'],
        'minimum_order' => $_POST['minimum_order']
    ];

    if ($productId) {
        $productData['status'] = $_POST['status'];
    }

    $imageFile = isset($_FILES['product_image']) ? $_FILES['product_image'] : null;

    if ($productId) {
        // Update existing product
        $result = $productService->updateProduct($productId, $productData, $imageFile);
    } else {
        // Create new product
        $result = $productService->createProduct($productData, $imageFile);
    }

    if (isset($result['success'])) {
        header('Location: products.php?success=1');
        exit;
    } else {
        $error = $result['error'];
    }
}

// Get categories for dropdown
$categories = $categoryService->getAllCategories();
?>

<head>
    <?php
    $title = $productId ? "Edit Product" : "Add New Product";
    $sub_title = "Products";
    include '../partials/title-meta.php'; ?>
    <?php include '../partials/head-css.php'; ?>
    <link rel="stylesheet" href="<?= asset_url('libs/selectize/css/selectize.bootstrap3.css') ?>">

    <style>
        /* Change height and text alignment */
        .selectize-control.single .selectize-input {
            height: 42px !important;
            padding: 8px 12px !important;
            font-size: 14px;
            line-height: 26px;
            border-radius: 4px;
        }

        /* Optional: Adjust dropdown options too */
        .selectize-dropdown-content>.option {
            padding: 10px 12px;
            font-size: 14px;
        }
    </style>

</head>

<body>
    <div id="wrapper">
        <?php include 'sidenav.php'; ?>
        <div class="content-page">
            <?php include '../partials/topbar.php'; ?>

            <div class="content">

                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);"><?php echo $sub_title ?? '' ?></a></li>
                                        <li class="breadcrumb-item active"><?php echo $title ?? '' ?></li>
                                    </ol>
                                </div>
                                <h4 class="page-title"><?php echo $productId ? "Edit Product" : "Add New Product"; ?></h4>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php
                            if (is_array($error)) {
                                echo '<ul class="mb-0">';
                                foreach ($error as $field => $messages) {
                                    foreach ($messages as $message) {
                                        echo '<li>' . htmlspecialchars($message) . '</li>';
                                    }
                                }
                                echo '</ul>';
                            } else {
                                echo htmlspecialchars($error);
                            }
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <?php if ($productId): ?>
                                            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                        <?php endif; ?>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                                    <input type="text"
                                                        class="form-control"
                                                        id="product_name"
                                                        name="product_name"
                                                        value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>"
                                                        required>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                                    <select id="category_id" name="category_id" required>
                                                        <option value="">Select Category</option>
                                                        <?php foreach ($categories as $category): ?>
                                                            <option value="<?php echo $category['id']; ?>"
                                                                <?php echo ($product['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($category['name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="product_price" class="form-label">Price <span class="text-danger">*</span></label>
                                                            <input type="number"
                                                                class="form-control"
                                                                id="product_price"
                                                                value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>"
                                                                required
                                                                name="product_price"
                                                                step="0.01">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="regular_price" class="form-label">Regular Price</label>
                                                            <input type="number"
                                                                class="form-control"
                                                                id="regular_price"
                                                                name="regular_price"
                                                                step="0.01"
                                                                value="<?php echo htmlspecialchars($product['regular_price'] ?? ''); ?>">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="stock" class="form-label">Stock <span class="text-danger">*</span></label>
                                                            <input type="number"
                                                                class="form-control"
                                                                id="stock"
                                                                name="stock"
                                                                value="<?php echo htmlspecialchars($product['stock'] ?? '0'); ?>"
                                                                required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                                                            <select class="form-select" id="unit" name="unit" required>
                                                                <option value="">Select Unit</option>
                                                                <option value="piece" <?php echo ($product['unit'] ?? '') === 'piece' ? 'selected' : ''; ?>>Piece</option>
                                                                <option value="kg" <?php echo ($product['unit'] ?? '') === 'kg' ? 'selected' : ''; ?>>Kilogram</option>
                                                                <option value="g" <?php echo ($product['unit'] ?? '') === 'g' ? 'selected' : ''; ?>>Gram</option>
                                                                <option value="l" <?php echo ($product['unit'] ?? '') === 'l' ? 'selected' : ''; ?>>Liter</option>
                                                                <option value="ml" <?php echo ($product['unit'] ?? '') === 'ml' ? 'selected' : ''; ?>>Milliliter</option>
                                                                <option value="pack" <?php echo ($product['unit'] ?? '') === 'pack' ? 'selected' : ''; ?>>Pack</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="minimum_order" class="form-label">Minimum Order</label>
                                                    <input type="number"
                                                        class="form-control"
                                                        id="minimum_order"
                                                        name="minimum_order"
                                                        value="<?php echo htmlspecialchars($product['minimum_order'] ?? '1'); ?>">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="product_description" class="form-label">Description</label>
                                                    <textarea class="form-control"
                                                        id="product_description"
                                                        name="product_description"
                                                        rows="5"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="product_image" class="form-label">Product Image</label>
                                                    <?php if (!empty($product['image_path'])): ?>
                                                        <div class="mb-2">
                                                            <img src="<?php echo base_url(htmlspecialchars($product['image_path'])); ?>"
                                                                alt="Current product image"
                                                                class="img-thumbnail"
                                                                style="max-height: 150px;">
                                                        </div>
                                                    <?php endif; ?>
                                                    <input type="file"
                                                        class="form-control"
                                                        id="product_image"
                                                        name="product_image"
                                                        accept="image/*">
                                                    <small class="form-text text-muted">
                                                        Leave empty to keep current image. Upload new image to replace.
                                                    </small>
                                                </div>

                                                <?php if ($productId): ?>
                                                    <div class="mb-3">
                                                        <label class="form-label">Status</label>
                                                        <div>
                                                            <div class="form-check form-check-inline">
                                                                <input type="radio"
                                                                    id="statusActive"
                                                                    name="status"
                                                                    class="form-check-input"
                                                                    value="active"
                                                                    <?php echo (!isset($product['status']) || $product['status'] === 'active') ? 'checked' : ''; ?>>
                                                                <label class="form-check-label" for="statusActive">Active</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input type="radio"
                                                                    id="statusInactive"
                                                                    name="status"
                                                                    class="form-check-input"
                                                                    value="inactive"
                                                                    <?php echo (isset($product['status']) && $product['status'] === 'inactive') ? 'checked' : ''; ?>>
                                                                <label class="form-check-label" for="statusInactive">Inactive</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input type="radio"
                                                                    id="statusOutOfStock"
                                                                    name="status"
                                                                    class="form-check-input"
                                                                    value="out_of_stock"
                                                                    <?php echo (isset($product['status']) && $product['status'] === 'out_of_stock') ? 'checked' : ''; ?>>
                                                                <label class="form-check-label" for="statusOutOfStock">Out of Stock</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="text-end">
                                            <a href="products.php" class="btn btn-secondary me-1">Cancel</a>
                                            <button type="submit" class="btn btn-primary">Save Product</button>
                                        </div>
                                    </form>
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

    <script>
        $(document).ready(function() {
            $('#category_id').selectize({
                create: false,
                sortField: 'text',

            });
        });
    </script>
</body>

</html>