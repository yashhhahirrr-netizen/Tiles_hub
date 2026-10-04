<?php
// admin/order-details.php
// Admin Single Order Detailed View & Status Updater

require_once __DIR__ . '/admin-header.php';

$orderId = (int)($_GET['id'] ?? 0);
$db = getDBConnection();

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $newOrderStatus = $_POST['order_status'] ?? '';
    $newPaymentStatus = $_POST['payment_status'] ?? '';

    $stmt = $db->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?");
    $stmt->execute([$newOrderStatus, $newPaymentStatus, $orderId]);

    // Also update payment record if payment status changed
    $stmt = $db->prepare("UPDATE payments SET status = ? WHERE order_id = ?");
    $stmt->execute([$newPaymentStatus, $orderId]);

    $message = "Order status updated to '{$newOrderStatus}' and payment status to '{$newPaymentStatus}'!";
}

$stmt = $db->prepare("SELECT o.*, i.invoice_number, u.full_name, u.email, u.phone 
                      FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      LEFT JOIN invoice_records i ON o.id = i.order_id 
                      WHERE o.id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: " . ADMIN_URL . "/orders.php");
    exit;
}

$stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

$statuses = ['Pending', 'Confirmed', 'Processing', 'Packed', 'Shipped', 'Out for Delivery', 'Delivered', 'Cancelled', 'Returned'];
$paymentStatuses = ['Pending', 'Paid', 'Failed', 'COD Pending', 'Refunded'];
?>

<div class="admin-topbar">
    <div>
        <h1 class="font-heading" style="font-size: 2rem;">Order <?php echo htmlspecialchars($order['order_number']); ?></h1>
        <p style="color: var(--admin-text-muted); font-size: 0.9rem;">Invoice #: <?php echo htmlspecialchars($order['invoice_number'] ?? 'N/A'); ?> &bull; Placed <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></p>
    </div>
    <div>
        <a href="<?php echo BASE_URL; ?>/invoice.php?id=<?php echo $order['id']; ?>" target="_blank" class="btn btn-primary btn-sm">View Tax Invoice</a>
        <a href="<?php echo BASE_URL; ?>/invoice.php?id=<?php echo $order['id']; ?>&action=pdf" class="btn btn-accent btn-sm">PDF Download</a>
    </div>
</div>

<?php if ($message): ?>
    <div style="background: #DCFCE7; color: #15803D; padding: 14px; margin-bottom: 24px; border-radius: 4px;">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 340px; gap: 32px;">
    <!-- Items & Shipping Info -->
    <div>
        <div style="background: #FFF; padding: 24px; border-radius: 8px; border: 1px solid var(--admin-border); margin-bottom: 24px;">
            <h3 class="font-heading" style="margin-bottom: 16px;">Ordered Tile Products</h3>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Tile Item</th>
                            <th>Size / Finish</th>
                            <th>Boxes</th>
                            <th>Coverage</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($it['product_name']); ?></strong>
                                    <div style="font-size: 0.78rem; color: var(--admin-text-muted);">SKU: <?php echo htmlspecialchars($it['sku']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($it['size']); ?><br><span style="font-size: 0.78rem;"><?php echo htmlspecialchars($it['finish']); ?></span></td>
                                <td><?php echo $it['boxes']; ?> Boxes</td>
                                <td><?php echo $it['coverage']; ?> sq.ft</td>
                                <td><?php echo formatPrice($it['unit_price']); ?></td>
                                <td><strong><?php echo formatPrice($it['total']); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px; font-size: 0.95rem; text-align: right;">
                <div>Subtotal: <?php echo formatPrice($order['subtotal']); ?></div>
                <?php if ($order['discount'] > 0): ?><div style="color: green;">Discount: -<?php echo formatPrice($order['discount']); ?></div><?php endif; ?>
                <div>GST (18%): <?php echo formatPrice($order['tax']); ?></div>
                <div>Freight Shipping: <?php echo formatPrice($order['shipping']); ?></div>
                <div style="font-size: 1.2rem; font-weight: 700; margin-top: 8px; color: var(--admin-primary);">Grand Total: <?php echo formatPrice($order['grand_total']); ?></div>
            </div>
        </div>

        <div style="background: #FFF; padding: 24px; border-radius: 8px; border: 1px solid var(--admin-border);">
            <h3 class="font-heading" style="margin-bottom: 16px;">Delivery Site Address</h3>
            <p style="font-size: 0.9rem; line-height: 1.7; color: var(--admin-text);">
                <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
            </p>
        </div>
    </div>

    <!-- Update Order & Payment Status Form -->
    <div style="background: #FFF; padding: 28px; border-radius: 8px; border: 1px solid var(--admin-border); height: fit-content;">
        <h3 class="font-heading" style="margin-bottom: 16px;">Update Status</h3>

        <form action="<?php echo ADMIN_URL; ?>/order-details.php?id=<?php echo $order['id']; ?>" method="POST">
            <input type="hidden" name="action" value="update_status">

            <div class="form-group">
                <label class="form-label">Order Fulfillment Status</label>
                <select name="order_status" class="form-control" required>
                    <?php foreach ($statuses as $st): ?>
                        <option value="<?php echo $st; ?>" <?php echo $order['order_status'] === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Payment Status</label>
                <select name="payment_status" class="form-control" required>
                    <?php foreach ($paymentStatuses as $pst): ?>
                        <option value="<?php echo $pst; ?>" <?php echo $order['payment_status'] === $pst ? 'selected' : ''; ?>><?php echo $pst; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-accent btn-block">Update Status</button>
        </form>

        <div style="margin-top: 24px; border-top: 1px solid var(--admin-border); padding-top: 16px; font-size: 0.85rem; color: var(--admin-text-muted);">
            Customer: <strong><?php echo htmlspecialchars($order['full_name']); ?></strong><br>
            Phone: <?php echo htmlspecialchars($order['phone']); ?><br>
            Email: <?php echo htmlspecialchars($order['email']); ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
