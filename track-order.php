<?php
// track-order.php
// Visual Tile Order Tracking Timeline

require_once __DIR__ . '/includes/header.php';

$orderNumber = trim($_GET['order_number'] ?? '');
$db = getDBConnection();
$order = null;
$items = [];

if (!empty($orderNumber)) {
    $stmt = $db->prepare("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.order_number = ?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();

    if ($order) {
        $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $items = $stmt->fetchAll();
    }
}

$statuses = ['Confirmed', 'Processing', 'Packed', 'Shipped', 'Out for Delivery', 'Delivered'];
$currentStatusIndex = array_search($order['order_status'] ?? '', $statuses);
if ($currentStatusIndex === false) $currentStatusIndex = 0;
?>

<div class="container section-padding">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 40px;">
        <span class="eyebrow">Real-Time Freight Tracker</span>
        <h1 class="font-heading">Track Your Tile Shipment</h1>
        <p class="subtitle">Enter your TilePoint Order Reference Number to track heavy crate dispatch & logistics status.</p>
    </div>

    <div style="max-width: 600px; margin: 0 auto 48px;">
        <form action="<?php echo BASE_URL; ?>/track-order.php" method="GET" style="display: flex; gap: 12px;">
            <input type="text" name="order_number" class="form-control" placeholder="e.g. TP-ORD-20261004-9912" value="<?php echo htmlspecialchars($orderNumber); ?>" required>
            <button type="submit" class="btn btn-primary">Track Order</button>
        </form>
    </div>

    <?php if (!empty($orderNumber) && !$order): ?>
        <div style="text-align: center; padding: 40px; background: var(--color-card); border-radius: var(--radius-md); border: 1px solid var(--color-border); max-width: 600px; margin: 0 auto;">
            <h3 class="font-heading" style="color: var(--color-danger);">Order Reference Not Found</h3>
            <p style="color: var(--color-text-muted);">Please double check your order number from your confirmation email or account dashboard.</p>
        </div>
    <?php elseif ($order): ?>
        <div style="background: var(--color-card); padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--color-border); max-width: 900px; margin: 0 auto;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--color-border);">
                <div>
                    <strong style="font-size: 1.2rem;"><?php echo htmlspecialchars($order['order_number']); ?></strong>
                    <div style="font-size: 0.85rem; color: var(--color-text-muted);">Placed on <?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                </div>
                <div>
                    <span class="badge-status <?php echo strtolower($order['order_status']); ?>" style="font-size: 0.9rem; padding: 6px 16px;"><?php echo htmlspecialchars($order['order_status']); ?></span>
                </div>
            </div>

            <!-- Visual Stepper Timeline -->
            <div class="timeline">
                <?php foreach ($statuses as $idx => $st): ?>
                    <div class="timeline-step <?php echo $idx <= $currentStatusIndex ? ($idx === $currentStatusIndex ? 'active' : 'completed') : ''; ?>">
                        <div class="step-node"><?php echo $idx <= $currentStatusIndex ? '✓' : ($idx + 1); ?></div>
                        <div style="font-size: 0.8rem; font-weight: 600;"><?php echo $st; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h4 class="font-heading" style="margin: 32px 0 16px;">Shipment Contents</h4>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Tile Item</th>
                            <th>Boxes</th>
                            <th>Total Sq. Ft.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($it['product_name']); ?> (<?php echo htmlspecialchars($it['size']); ?>)</td>
                                <td><?php echo $it['boxes']; ?> Boxes</td>
                                <td><?php echo $it['coverage']; ?> sq. ft.</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
