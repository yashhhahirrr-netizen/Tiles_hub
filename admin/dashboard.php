<?php
// admin/dashboard.php
// Main Admin Metrics Dashboard

require_once __DIR__ . '/admin-header.php';
$db = getDBConnection();

// Fetch metrics
$totalSales = $db->query("SELECT COALESCE(SUM(grand_total), 0) FROM orders WHERE payment_status = 'Paid' OR order_status = 'Delivered'")->fetchColumn();
$totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders = $db->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('Pending', 'Confirmed', 'Processing')")->fetchColumn();
$completedOrders = $db->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Delivered'")->fetchColumn();
$totalCustomers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$lowStockCount = $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity < 20")->fetchColumn();
$sampleCount = $db->query("SELECT COUNT(*) FROM sample_requests WHERE status = 'Submitted'")->fetchColumn();

// Fetch 10 recent orders
$recentOrders = $db->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC LIMIT 10")->fetchAll();
?>

<div class="admin-topbar">
    <div>
        <h1 class="font-heading" style="font-size: 2rem;">Executive Dashboard</h1>
        <p style="color: var(--admin-text-muted); font-size: 0.9rem;">Real-time database metrics for TilePoint surfaces & customer transactions.</p>
    </div>
    <div>
        <a href="<?php echo ADMIN_URL; ?>/product-create.php" class="btn btn-accent btn-sm">+ Add New Tile Surface</a>
    </div>
</div>

<!-- Key Stat Cards Grid -->
<div class="stat-grid">
    <div class="stat-card">
        <span style="font-size: 0.8rem; color: var(--admin-text-muted); font-weight: 600; text-transform: uppercase;">Total Revenue</span>
        <div class="num"><?php echo formatPrice($totalSales); ?></div>
        <span style="font-size: 0.78rem; color: #15803D;">Verified Paid / Delivered Orders</span>
    </div>

    <div class="stat-card">
        <span style="font-size: 0.8rem; color: var(--admin-text-muted); font-weight: 600; text-transform: uppercase;">Total Orders</span>
        <div class="num"><?php echo $totalOrders; ?></div>
        <span style="font-size: 0.78rem; color: #A16207;"><?php echo $pendingOrders; ?> Orders Pending Dispatch</span>
    </div>

    <div class="stat-card">
        <span style="font-size: 0.8rem; color: var(--admin-text-muted); font-weight: 600; text-transform: uppercase;">Tile Catalog</span>
        <div class="num"><?php echo $totalProducts; ?></div>
        <span style="font-size: 0.78rem; color: #B91C1C;"><?php echo $lowStockCount; ?> Low Stock (&lt; 20 Boxes)</span>
    </div>

    <div class="stat-card">
        <span style="font-size: 0.8rem; color: var(--admin-text-muted); font-weight: 600; text-transform: uppercase;">Sample Requests</span>
        <div class="num"><?php echo $sampleCount; ?></div>
        <span style="font-size: 0.78rem; color: #2563EB;">Pending Specimen Dispatch</span>
    </div>
</div>

<!-- Recent Orders Table -->
<div style="margin-top: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 class="font-heading">Recent Customer Orders</h3>
        <a href="<?php echo ADMIN_URL; ?>/orders.php" style="font-size: 0.88rem; color: var(--admin-accent); font-weight: 600;">View All Orders &rarr;</a>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $ord): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($ord['order_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($ord['full_name']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($ord['payment_method']); ?> (<?php echo htmlspecialchars($ord['payment_status']); ?>)</td>
                        <td><span class="badge-status <?php echo strtolower($ord['order_status']); ?>"><?php echo htmlspecialchars($ord['order_status']); ?></span></td>
                        <td><strong><?php echo formatPrice($ord['grand_total']); ?></strong></td>
                        <td>
                            <a href="<?php echo ADMIN_URL; ?>/order-details.php?id=<?php echo $ord['id']; ?>" class="btn btn-outline btn-sm">Manage Order</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
