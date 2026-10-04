<?php
// admin/admin-header.php
// Central Admin Layout Header

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/admin-auth.php';

requireAdminLogin();

$adminUser = getLoggedInAdmin();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | TilePoint</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body class="admin-body">

<!-- Sidebar Navigation -->
<aside class="admin-sidebar">
    <div class="admin-brand">
        TILEPOINT <span style="font-size: 0.65rem; color: var(--admin-accent); display: block;">ADMIN PORTAL</span>
    </div>

    <ul class="admin-nav">
        <li><a href="<?php echo ADMIN_URL; ?>/dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">📊 Dashboard</a></li>
        <li><a href="<?php echo ADMIN_URL; ?>/products.php" class="<?php echo strpos($currentPage, 'product') !== false ? 'active' : ''; ?>">🧱 Tile Products</a></li>
        <li><a href="<?php echo ADMIN_URL; ?>/categories.php" class="<?php echo $currentPage === 'categories.php' ? 'active' : ''; ?>">🏷️ Categories</a></li>
        <li><a href="<?php echo ADMIN_URL; ?>/orders.php" class="<?php echo strpos($currentPage, 'order') !== false ? 'active' : ''; ?>">📦 Orders</a></li>
        <li><a href="<?php echo ADMIN_URL; ?>/sample-requests.php" class="<?php echo $currentPage === 'sample-requests.php' ? 'active' : ''; ?>">📦 Sample Requests</a></li>
        <li><a href="<?php echo ADMIN_URL; ?>/customers.php" class="<?php echo $currentPage === 'customers.php' ? 'active' : ''; ?>">👥 Customers</a></li>
        <li><a href="<?php echo ADMIN_URL; ?>/coupons.php" class="<?php echo $currentPage === 'coupons.php' ? 'active' : ''; ?>">🎟️ Coupons</a></li>
        <li><a href="<?php echo ADMIN_URL; ?>/reviews.php" class="<?php echo $currentPage === 'reviews.php' ? 'active' : ''; ?>">⭐ Reviews</a></li>
        <li><a href="<?php echo ADMIN_URL; ?>/payments.php" class="<?php echo $currentPage === 'payments.php' ? 'active' : ''; ?>">💳 Payments</a></li>
        <li><a href="<?php echo ADMIN_URL; ?>/invoices.php" class="<?php echo $currentPage === 'invoices.php' ? 'active' : ''; ?>">📄 Invoices</a></li>
        <li><a href="<?php echo ADMIN_URL; ?>/settings.php" class="<?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">⚙️ Store Settings</a></li>
    </ul>

    <div style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 0.85rem;">
        <span style="color: #94A3B8; display: block;">Logged in as:</span>
        <strong style="color: #FFF; display: block; margin-bottom: 8px;"><?php echo htmlspecialchars($adminUser['full_name']); ?></strong>
        <a href="<?php echo ADMIN_URL; ?>/logout.php" style="color: #F87171; text-decoration: none;">Sign Out</a>
    </div>
</aside>

<main class="admin-main">
