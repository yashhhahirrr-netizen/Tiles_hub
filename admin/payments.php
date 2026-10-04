<?php
// admin/payments.php
// Payment Transaction Log Audit

require_once __DIR__ . '/admin-header.php';
$db = getDBConnection();

$payments = $db->query("SELECT p.*, o.order_number, u.full_name, u.email 
                        FROM payments p 
                        JOIN orders o ON p.order_id = o.id 
                        JOIN users u ON p.user_id = u.id 
                        ORDER BY p.id DESC")->fetchAll();
?>

<div class="admin-topbar">
    <div>
        <h1 class="font-heading" style="font-size: 2rem;">Payment Logs & Audit</h1>
        <p style="color: var(--admin-text-muted); font-size: 0.9rem;">Review Cash on Delivery and online gateway payment logs.</p>
    </div>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Txn ID</th>
                <th>Order #</th>
                <th>Customer</th>
                <th>Gateway / Method</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $pay): ?>
                <tr>
                    <td><code><?php echo htmlspecialchars($pay['transaction_id'] ?? 'N/A'); ?></code></td>
                    <td><strong><?php echo htmlspecialchars($pay['order_number']); ?></strong></td>
                    <td><?php echo htmlspecialchars($pay['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($pay['payment_method']); ?> (<?php echo htmlspecialchars($pay['gateway']); ?>)</td>
                    <td><strong><?php echo formatPrice($pay['amount']); ?></strong></td>
                    <td><span class="badge-status <?php echo strtolower($pay['status']); ?>"><?php echo htmlspecialchars($pay['status']); ?></span></td>
                    <td><?php echo date('M d, Y H:i', strtotime($pay['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
