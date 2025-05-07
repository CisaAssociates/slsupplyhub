<!-- ========== Left Sidebar Start ========== -->
<div class="app-menu">
    <!-- Brand Logo -->
    <div class="logo-box">
        <a href="index.php" class="logo-light">
            <span class="logo-sm">
                <img src="<?= asset_url() ?>images/logo-sm.png" alt="small logo" style="height: 50px;">
            </span>
            <span class="logo-lg">
                <img src="<?= asset_url() ?>images/logo-light.png" alt="large logo" style="height: 40px;">
            </span>
        </a>
        <a href="index.php" class="logo-dark">
            <span class="logo-sm">
                <img src="<?= asset_url() ?>images/logo-sm.png" alt="small logo" style="height: 50px;">
            </span>
            <span class="logo-lg">
                <img src="<?= asset_url() ?>images/logo-dark.png" alt="large logo" style="height: 40px;">
            </span>
        </a>
    </div>

    <!--- Sidemenu -->
    <div class="scrollbar">
        <ul class="menu">
            <li class="menu-item">
                <a href="index.php" class="menu-link">
                    <i class="mdi mdi-store menu-icon"></i>
                    <span class="menu-text">Shop</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="cart.php" class="menu-link">
                    <i class="mdi mdi-cart menu-icon"></i>
                    <span class="menu-text">Cart</span>
                    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="badge bg-success float-end cart-count"><?php echo count($_SESSION['cart']); ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="orders.php" class="menu-link">
                    <i class="mdi mdi-package-variant menu-icon"></i>
                    <span class="menu-text">My Orders</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="address.php" class="menu-link">
                    <i class="mdi mdi-map-marker menu-icon"></i>
                    <span class="menu-text">My Addresses</span>
                </a>
            </li>

            <!-- <li class="menu-item">
                <a href="wishlist.php" class="menu-link">
                    <i class="mdi mdi-heart menu-icon"></i>
                    <span class="menu-text">Wishlist</span>
                    <?php if (isset($wishlistCount) && $wishlistCount > 0): ?>
                        <span class="badge bg-danger float-end wishlist-count"><?php echo $wishlistCount; ?></span>
                    <?php endif; ?>
                </a>
            </li> -->
        </ul>
    </div>
</div>
<!-- ========== Left Sidebar End ========== -->