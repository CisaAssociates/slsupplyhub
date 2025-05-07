<?php
include '../partials/main.php';

if (!$session->getUserId() || $session->getUserRole() !== 'supplier') {
    header('Location: ../auth-login.php');
    exit;
}

$supplierId = $session->getUserId();

$productService = new SLSupplyHub\Product();

// Get current page and filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$filters = [
    'search' => $_GET['search'] ?? '',
    'sort' => $_GET['sort'] ?? 'newest',
    'status' => $_GET['status'] ?? 'all'
];

// Get products with pagination
$products = $productService->getProductsBySupplier($supplierId, $page);
?>

<head>
    <?php
    $title = "Products Management";
    $sub_title = "Menu";
    $page_title = "Products";
    include '../partials/title-meta.php'; ?>
    <?php include '../partials/head-css.php'; ?>
    <link rel="stylesheet" href="<?= asset_url('libs/sweetalert2/sweetalert2.min.css') ?>">
    <script src="<?= asset_url('libs/sweetalert2/sweetalert2.min.js') ?>"></script>
</head>

<body>
    <div id="wrapper">
        <?php include 'sidenav.php'; ?>
        <div class="content-page">
            <?php include '../partials/topbar.php'; ?>
            
            <div class="content">
                <div class="container-fluid">
                    
                    <?php include '../partials/page-title.php'; ?>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Product has been successfully saved!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['deleted'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Product has been successfully deleted!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row justify-content-between mb-2">
                                        <div class="col-auto">
                                            <form action="" method="GET" class="d-flex flex-wrap align-items-center">
                                                <div class="me-3">
                                                    <input type="search" 
                                                           class="form-control my-1 my-lg-0" 
                                                           id="search" 
                                                           name="search"
                                                           value="<?php echo htmlspecialchars($filters['search']); ?>"
                                                           placeholder="Search...">
                                                </div>
                                                <div class="me-sm-3">
                                                    <select class="form-select my-1 my-lg-0" id="sort" name="sort">
                                                        <option value="newest" <?php echo $filters['sort'] === 'newest' ? 'selected' : ''; ?>>Newest</option>
                                                        <option value="price_low" <?php echo $filters['sort'] === 'price_low' ? 'selected' : ''; ?>>Price Low to High</option>
                                                        <option value="price_high" <?php echo $filters['sort'] === 'price_high' ? 'selected' : ''; ?>>Price High to Low</option>
                                                        <option value="name" <?php echo $filters['sort'] === 'name' ? 'selected' : ''; ?>>Name</option>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Filter</button>
                                            </form>
                                        </div>
                                        <div class="col-auto">
                                            <div class="text-lg-end my-1 my-lg-0">
                                                <a href="product-edit.php" class="btn btn-danger waves-effect waves-light">
                                                    <i class="mdi mdi-plus-circle me-1"></i> Add New Product
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-centered table-nowrap table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Image</th>
                                                    <th>Product Name</th>
                                                    <th>Price</th>
                                                    <th>Stock</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($products['items'])): ?>
                                                    <?php foreach ($products['items'] as $product): ?>
                                                        <tr>
                                                            <td>
                                                                <?php if ($product['image_path']): ?>
                                                                    <img src="<?php echo base_url(htmlspecialchars($product['image_path'], ENT_QUOTES, 'UTF-8')) ?>" 
                                                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                                         class="rounded-circle"
                                                                         height="48">
                                                                <?php else: ?>
                                                                    <div class="avatar-sm">
                                                                        <span class="avatar-title bg-light text-secondary rounded-circle">
                                                                            No img
                                                                        </span>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <h5 class="font-14 my-1">
                                                                    <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="text-body">
                                                                        <?php echo htmlspecialchars($product['name']); ?>
                                                                    </a>
                                                                </h5>
                                                                <span class="text-muted font-13">
                                                                    Added <?php echo date('M j, Y', strtotime($product['created_at'])); ?>
                                                                </span>
                                                            </td>
                                                            <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                                            <td>
                                                                <?php if ($product['stock'] <= 5): ?>
                                                                    <span class="text-danger"><?php echo $product['stock']; ?></span>
                                                                <?php else: ?>
                                                                    <?php echo $product['stock']; ?>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-<?php 
                                                                    echo match($product['status']) {
                                                                        'active' => 'success',
                                                                        'inactive' => 'warning',
                                                                        'out_of_stock' => 'danger',
                                                                        default => 'secondary'
                                                                    };
                                                                ?>">
                                                                    <?php echo ucfirst($product['status']); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <a href="product-edit.php?id=<?php echo $product['id']; ?>" 
                                                                   class="action-icon">
                                                                    <i class="mdi mdi-square-edit-outline"></i>
                                                                </a>
                                                                <a href="javascript:void(0);" 
                                                                   class="action-icon"
                                                                   onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                                                    <i class="mdi mdi-delete"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">No products found</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <?php if ($products['last_page'] > 1): ?>
                                    <div class="row mt-4">
                                        <div class="col-sm-12 col-md-5">
                                            <div class="dataTables_info">
                                                Showing <?php echo ($products['current_page'] - 1) * $products['per_page'] + 1; ?> to 
                                                <?php echo min($products['current_page'] * $products['per_page'], $products['total']); ?> of 
                                                <?php echo $products['total']; ?> entries
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-7">
                                            <div class="d-flex justify-content-end">
                                                <nav>
                                                    <ul class="pagination pagination-rounded mb-0">
                                                        <?php if ($products['current_page'] > 1): ?>
                                                            <li class="page-item">
                                                                <a class="page-link" href="?page=<?php echo $products['current_page'] - 1; ?>&<?php echo http_build_query($filters); ?>">
                                                                    <i class="mdi mdi-chevron-left"></i>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>

                                                        <?php for ($i = 1; $i <= $products['last_page']; $i++): ?>
                                                            <li class="page-item <?php echo $i === $products['current_page'] ? 'active' : ''; ?>">
                                                                <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>">
                                                                    <?php echo $i; ?>
                                                                </a>
                                                            </li>
                                                        <?php endfor; ?>

                                                        <?php if ($products['current_page'] < $products['last_page']): ?>
                                                            <li class="page-item">
                                                                <a class="page-link" href="?page=<?php echo $products['current_page'] + 1; ?>&<?php echo http_build_query($filters); ?>">
                                                                    <i class="mdi mdi-chevron-right"></i>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </nav>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
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

    <script>
    function deleteProduct(productId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'product-delete.php?id=' + productId;
            }
        });
    }
    </script>
</body>
</html>