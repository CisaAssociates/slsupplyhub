<!-- ========== Menu ========== -->
<div class="app-menu">  

    <!-- Brand Logo -->
    <div class="logo-box">
        <!-- Brand Logo Light -->
        <a href="index.php" class="logo-light">
            <img src="<?= asset_url() ?>images/logo-light.png" alt="logo" class="logo-lg" style="height: 50px;">
            <img src="<?= asset_url() ?>images/logo-sm.png" alt="small logo" class="logo-sm" style="height: 40px;">
        </a>

        <!-- Brand Logo Dark -->
        <a href="index.php" class="logo-dark">
            <img src="<?= asset_url() ?>images/logo-dark.png" alt="dark logo" class="logo-lg" style="height: 50px;">
            <img src="<?= asset_url() ?>images/logo-sm.png" alt="small logo" class="logo-sm" style="height: 40px;">
        </a>
    </div>

    <!-- menu-left -->
    <div class="scrollbar">

        <!-- User box -->
        <div class="user-box text-center">
            <img src="<?= asset_url() ?>images/users/user-1.jpg" alt="user-img" title="Mat Helme" class="rounded-circle avatar-md">
            <div class="dropdown">
                <a href="javascript: void(0);" class="dropdown-toggle h5 mb-1 d-block" data-bs-toggle="dropdown">Geneva Kennedy</a>
            </div>
            <p class="text-muted mb-0">Supplier</p>
        </div>

        <!--- Menu -->
        <ul class="menu">

            <li class="menu-title">Home</li>
            <li class="menu-item">
                <a href="<?= base_url('/supplier/') ?>" class="menu-link">
                    <i class="menu-icon mdi mdi-view-dashboard"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>

            <li class="menu-title">Menu</li>

            <li class="menu-item">
                <a href="orders.php" class="menu-link">
                    <i class="menu-icon mdi mdi-cart"></i>
                    <span class="menu-text">Orders</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="products.php" class="menu-link">
                    <i class="menu-icon mdi mdi-package-variant"></i>
                    <span class="menu-text">Products</span>
                </a>
            </li>

            <li class="menu-title">Settings</li>
            
            <li class="menu-item">
                <a href="manage.php" class="menu-link">
                    <i class="menu-icon mdi mdi-store"></i>
                    <span class="menu-text">Business Profile</span>
                </a>
            </li>
        </ul>
        <!--- End Menu -->
        <div class="clearfix"></div>
    </div>
</div>
<!-- ========== Left menu End ========== -->