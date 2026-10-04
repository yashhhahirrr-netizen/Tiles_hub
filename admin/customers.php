<?php
// admin/customers.php
// Customer Management

require_once __DIR__ . '/admin-header.php';
$db = getDBConnection();

$customers = $db->query("SELECT u.*, COUNT(o.id) AS total_orders, COALESCE(SUM(o.grand_total), 0) AS total_spent 
                         FROM users u 
                         LEFT JOIN orders o ON u.id = o.user_id 
                         GROUP BY u.id 
                         ORDER BY u.id DESC")->fetchAll();
?>

<div class="admin-topbar">
    <div>
        <h1 class="font-heading" style="font-size: 2rem;">Customer Management</h1>
        <p style="color: var(--admin-text-muted); font-size: 0.9rem;">View registered clients, order counts, and lifetime purchase metrics.</p>
    </div>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Customer ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Orders Placed</th>
                <th>Total Spent</th>
                <th>Status</th>
                <th>Registered Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $c): ?>
                <tr>
                    <td>#USR-<?php echo $c['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($c['full_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($c['email']); ?></td>
                    <td><?php echo htmlspecialchars($c['phone'] ?? 'N/A'); ?></td>
                    <td><?php echo $c['total_orders']; ?> Orders</td>
                    <td><strong><?php echo formatPrice($c['total_spent']); ?></strong></td>
                    <td><span class="badge-status <?php echo strtolower($c['status']); ?>"><?php echo htmlspecialchars($c['status']); ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($c['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
