<?php
// admin/orders.php
// Admin Order Management List

require_once __DIR__ . '/admin-header.php';
$db = getDBConnection();

$statusFilter = trim($_GET['status'] ?? '');
$where = "WHERE 1=1";
$params = [];

if ($statusFilter) {
    $where .= " AND o.order_status = :st";
    $params[':st'] = $statusFilter;
}

$stmt = $db->prepare("SELECT o.*, u.full_name, u.email, u.phone 
                      FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      {$where} 
                      ORDER BY o.id DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<div class="admin-topbar">
    <div>
        <h1 class="font-heading" style="font-size: 2rem;">Order Management</h1>
        <p style="color: var(--admin-text-muted); font-size: 0.9rem;">Track customer orders, update shipping progress, and generate tax invoices.</p>
    </div>
</div>

<div style="margin-bottom: 24px; display: flex; gap: 12px; align-items: center;">
    <span style="font-weight: 500; font-size: 0.85rem;">Filter Status:</span>
    <a href="<?php echo ADMIN_URL; ?>/orders.php" class="btn btn-outline btn-sm">All</a>
    <a href="<?php echo ADMIN_URL; ?>/orders.php?status=Confirmed" class="btn btn-outline btn-sm">Confirmed</a>
    <a href="<?php echo ADMIN_URL; ?>/orders.php?status=Processing" class="btn btn-outline btn-sm">Processing</a>
    <a href="<?php echo ADMIN_URL; ?>/orders.php?status=Shipped" class="btn btn-outline btn-sm">Shipped</a>
    <a href="<?php echo ADMIN_URL; ?>/orders.php?status=Delivered" class="btn btn-outline btn-sm">Delivered</a>
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
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $ord): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($ord['order_number']); ?></strong></td>
                    <td>
                        <strong><?php echo htmlspecialchars($ord['full_name']); ?></strong>
                        <div style="font-size: 0.78rem; color: var(--admin-text-muted);"><?php echo htmlspecialchars($ord['phone']); ?></div>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></td>
                    <td>
                        <?php echo htmlspecialchars($ord['payment_method']); ?>
                        <div style="font-size: 0.75rem;"><span class="badge-status <?php echo strtolower($ord['payment_status']); ?>"><?php echo htmlspecialchars($ord['payment_status']); ?></span></div>
                    </td>
                    <td><span class="badge-status <?php echo strtolower($ord['order_status']); ?>"><?php echo htmlspecialchars($ord['order_status']); ?></span></td>
                    <td><strong><?php echo formatPrice($ord['grand_total']); ?></strong></td>
                    <td>
                        <a href="<?php echo ADMIN_URL; ?>/order-details.php?id=<?php echo $ord['id']; ?>" class="btn btn-primary btn-sm">Details & Status</a>
                        <a href="<?php echo BASE_URL; ?>/invoice.php?id=<?php echo $ord['id']; ?>" target="_blank" class="btn btn-outline btn-sm">Invoice</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
