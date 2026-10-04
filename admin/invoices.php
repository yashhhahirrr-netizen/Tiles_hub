<?php
// admin/invoices.php
// Admin Invoice Lookup & PDF Downloader

require_once __DIR__ . '/admin-header.php';
$db = getDBConnection();

$invoices = $db->query("SELECT i.*, o.order_number, o.grand_total, o.payment_status, u.full_name 
                        FROM invoice_records i 
                        JOIN orders o ON i.order_id = o.id 
                        JOIN users u ON i.user_id = u.id 
                        ORDER BY i.id DESC")->fetchAll();
?>

<div class="admin-topbar">
    <div>
        <h1 class="font-heading" style="font-size: 2rem;">Tax Invoices Management</h1>
        <p style="color: var(--admin-text-muted); font-size: 0.9rem;">Lookup and download generated PDF invoices for all verified orders.</p>
    </div>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Order #</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Payment Status</th>
                <th>Generated Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoices as $inv): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($inv['invoice_number']); ?></strong></td>
                    <td><?php echo htmlspecialchars($inv['order_number']); ?></td>
                    <td><?php echo htmlspecialchars($inv['full_name']); ?></td>
                    <td><strong><?php echo formatPrice($inv['grand_total']); ?></strong></td>
                    <td><span class="badge-status <?php echo strtolower($inv['payment_status']); ?>"><?php echo htmlspecialchars($inv['payment_status']); ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($inv['generated_at'])); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>/invoice.php?id=<?php echo $inv['order_id']; ?>" target="_blank" class="btn btn-outline btn-sm">View</a>
                        <a href="<?php echo BASE_URL; ?>/invoice.php?id=<?php echo $inv['order_id']; ?>&action=pdf" class="btn btn-accent btn-sm">Download PDF</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
