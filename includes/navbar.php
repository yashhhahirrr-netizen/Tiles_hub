<?php
// includes/navbar.php
// Central Luxury Navigation Component for TilePoint

$cartItems = getCartItems();
$cartCount = array_sum(array_column($cartItems, 'boxes'));
$wishlistCount = 0;
if (isLoggedIn()) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $wishlistCount = $stmt->fetchColumn();
}
$currentUser = getLoggedInUser();
?>

<!-- Announcement Top Bar -->
<div class="top-bar">
    <div class="container d-flex justify-content-between align-items-center">
        <span>Architectural Grade Vitrified & Porcelain Surfaces | Complimentary Sample Delivery Across India</span>
        <span>Customer Concierge: +91 98765 43210</span>
    </div>
</div>

<header class="site-header">
    <div class="container">
        <div class="navbar-inner">
            <!-- Brand Logo -->
            <a href="<?php echo BASE_URL; ?>/index.php" class="brand-logo">
                <span class="logo-main">TILEPOINT</span>
                <span class="logo-sub">PREMIUM TILES & SURFACES</span>
            </a>

            <!-- Desktop Navigation -->
            <nav>
                <ul class="nav-links">
                    <li><a href="<?php echo BASE_URL; ?>/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/shop.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'shop.php' ? 'active' : ''; ?>">Tile Collections</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/tile-calculator.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'tile-calculator.php' ? 'active' : ''; ?>">Tile Calculator</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/sample-request.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sample-request.php' ? 'active' : ''; ?>">Request Samples</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/room-guide.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'room-guide.php' ? 'active' : ''; ?>">Application Guide</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">About Us</a></li>
                </ul>
            </nav>

            <!-- Actions (Search, Wishlist, Cart, User) -->
            <div class="nav-actions">
                <button class="icon-btn js-search-open" title="Search Tiles">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>

                <a href="<?php echo BASE_URL; ?>/wishlist.php" class="icon-btn" title="Wishlist">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    <span class="badge-count js-wishlist-count"><?php echo $wishlistCount; ?></span>
                </a>

                <a href="<?php echo BASE_URL; ?>/cart.php" class="icon-btn" title="Cart">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span class="badge-count js-cart-count"><?php echo $cartCount; ?></span>
                </a>

                <div class="user-menu">
                    <?php if ($currentUser): ?>
                        <button class="icon-btn" title="My Account">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </button>
                        <div class="dropdown-menu">
                            <a href="<?php echo BASE_URL; ?>/account.php">Overview & Profile</a>
                            <a href="<?php echo BASE_URL; ?>/account.php?tab=orders">My Tile Orders</a>
                            <a href="<?php echo BASE_URL; ?>/account.php?tab=samples">Sample Requests</a>
                            <a href="<?php echo BASE_URL; ?>/account.php?tab=addresses">Saved Addresses</a>
                            <hr style="margin: 4px 0; border: none; border-top: 1px solid var(--color-border);">
                            <a href="<?php echo BASE_URL; ?>/account.php?action=logout" style="color: var(--color-danger);">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline btn-sm">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Search Overlay Modal -->
<div id="search-overlay" class="search-overlay">
    <div class="search-container">
        <button class="icon-btn js-search-close" style="position: absolute; top: 16px; right: 16px;">✕</button>
        <h3 class="font-heading" style="margin-bottom: 16px;">Search Premium Surfaces</h3>
        <form action="<?php echo BASE_URL; ?>/search.php" method="GET">
            <div class="search-input-group">
                <input type="text" id="search-input" name="q" placeholder="Type tile name, material, color, size, or SKU..." required>
                <button type="submit" class="btn btn-accent">Search</button>
            </div>
        </form>
        <div style="margin-top: 20px; font-size: 0.85rem; color: var(--color-text-muted);">
            Popular: Carrara Marble, 600x1200 mm, Anti-Skid Bathroom, Nordic Oak Wood, Vitrified Slabs
        </div>
    </div>
</div>
