<?php
// order-success.php
// Order Confirmation & Invoice Action Center

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin('order-success.php');

$orderId = (int)($_GET['order_id'] ?? 0);
$db = getDBConnection();
$currentUser = getLoggedInUser();

$stmt = $db->prepare("SELECT o.*, i.invoice_number 
                      FROM orders o 
                      LEFT JOIN invoice_records i ON o.id = i.order_id 
                      WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$orderId, $currentUser['id']]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: " . BASE_URL . "/account.php");
    exit;
}

$stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll();
?>

<div class="container section-padding">
    <div style="max-width: 800px; margin: 0 auto; background: var(--color-card); padding: 48px; border-radius: var(--radius-lg); border: 1px solid var(--color-border); box-shadow: var(--shadow-md); text-align: center;">
        <div style="width: 70px; height: 70px; background: #DCFCE7; color: #15803D; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; margin: 0 auto 20px;">✓</div>
        <span class="eyebrow" style="color: var(--color-success);">Order Successfully Confirmed</span>
        <h1 class="font-heading" style="margin-bottom: 12px;">Thank You For Your Order!</h1>
        <p style="color: var(--color-text-muted); font-size: 1.05rem; margin-bottom: 32px;">
            Order Reference: <strong><?php echo htmlspecialchars($order['order_number']); ?></strong> &bull; Invoice #: <strong><?php echo htmlspecialchars($order['invoice_number'] ?? 'N/A'); ?></strong>
        </p>

        <!-- Invoice & Tracking Action Buttons -->
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; margin-bottom: 40px;">
            <a href="<?php echo BASE_URL; ?>/invoice.php?id=<?php echo $orderId; ?>" target="_blank" class="btn btn-primary btn-sm">View Tax Invoice</a>
            <a href="<?php echo BASE_URL; ?>/invoice.php?id=<?php echo $orderId; ?>&action=pdf" class="btn btn-accent btn-sm">Download PDF Invoice</a>
            <a href="<?php echo BASE_URL; ?>/track-order.php?order_number=<?php echo htmlspecialchars($order['order_number']); ?>" class="btn btn-outline btn-sm">Track Shipping Status</a>
        </div>

        <!-- Order Items Detail -->
        <div class="admin-table-wrap" style="text-align: left; margin-bottom: 32px;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tile Product</th>
                        <th>Size / Finish</th>
                        <th>Box Count</th>
                        <th>Coverage</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                <div style="font-size: 0.78rem; color: var(--color-text-muted);">SKU: <?php echo htmlspecialchars($item['sku']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($item['size']); ?> — <?php echo htmlspecialchars($item['finish']); ?></td>
                            <td><?php echo $item['boxes']; ?> Boxes</td>
                            <td><?php echo $item['coverage']; ?> sq. ft.</td>
                            <td><strong><?php echo formatPrice($item['total']); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 700; background: var(--color-bg-alt); padding: 16px 24px; border-radius: var(--radius-md);">
            <span>Grand Total Paid / Payable (<?php echo htmlspecialchars($order['payment_method']); ?>):</span>
            <span><?php echo formatPrice($order['grand_total']); ?></span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
