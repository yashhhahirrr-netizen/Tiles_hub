<?php
// admin/coupons.php
// Coupon Code Management

require_once __DIR__ . '/admin-header.php';
$db = getDBConnection();

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $type = $_POST['discount_type'] ?? 'percentage';
    $val = (float)($_POST['discount_value'] ?? 0);
    $minOrder = (float)($_POST['minimum_order'] ?? 0);
    $expiry = $_POST['expiry_date'] ?? date('Y-12-31');

    if (!empty($code) && $val > 0) {
        $stmt = $db->prepare("INSERT INTO coupons (code, discount_type, discount_value, minimum_order, expiry_date, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$code, $type, $val, $minOrder, $expiry]);
        $message = "Coupon code '{$code}' created!";
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $cId = (int)$_GET['id'];
    $stmt = $db->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->execute([$cId]);
    $message = "Coupon deleted.";
}

$coupons = $db->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
?>

<div class="admin-topbar">
    <div>
        <h1 class="font-heading" style="font-size: 2rem;">Coupon & Promo Management</h1>
        <p style="color: var(--admin-text-muted); font-size: 0.9rem;">Create percentage or fixed discount promo codes for tile orders.</p>
    </div>
</div>

<?php if ($message): ?>
    <div style="background: #DCFCE7; color: #15803D; padding: 14px; margin-bottom: 24px; border-radius: 4px;">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 340px 1fr; gap: 32px;">
    <!-- Create Coupon Form -->
    <div style="background: #FFF; padding: 28px; border-radius: 8px; border: 1px solid var(--admin-border); height: fit-content;">
        <h3 class="font-heading" style="margin-bottom: 16px;">Create Coupon Code</h3>
        <form action="<?php echo ADMIN_URL; ?>/coupons.php" method="POST">
            <input type="hidden" name="action" value="create">
            <div class="form-group">
                <label class="form-label">Coupon Code *</label>
                <input type="text" name="code" class="form-control" required placeholder="e.g. LUXETILES10">
            </div>
            <div class="form-group">
                <label class="form-label">Discount Type</label>
                <select name="discount_type" class="form-control">
                    <option value="percentage">Percentage (%)</option>
                    <option value="fixed">Fixed Amount (₹)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Discount Value *</label>
                <input type="number" step="0.01" name="discount_value" class="form-control" required placeholder="10.00">
            </div>
            <div class="form-group">
                <label class="form-label">Minimum Order (₹)</label>
                <input type="number" step="0.01" name="minimum_order" class="form-control" value="0.00">
            </div>
            <div class="form-group">
                <label class="form-label">Expiry Date</label>
                <input type="date" name="expiry_date" class="form-control" value="<?php echo date('Y-12-31'); ?>">
            </div>
            <button type="submit" class="btn btn-accent btn-block">Create Coupon</button>
        </form>
    </div>

    <!-- Coupons Table -->
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Min Order</th>
                    <th>Used</th>
                    <th>Expiry</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($coupons as $c): ?>
                    <tr>
                        <td><strong><code><?php echo htmlspecialchars($c['code']); ?></code></strong></td>
                        <td><?php echo ucfirst($c['discount_type']); ?></td>
                        <td><?php echo $c['discount_type'] === 'percentage' ? $c['discount_value'] . '%' : formatPrice($c['discount_value']); ?></td>
                        <td><?php echo formatPrice($c['minimum_order']); ?></td>
                        <td><?php echo $c['used_count']; ?> times</td>
                        <td><?php echo date('M d, Y', strtotime($c['expiry_date'])); ?></td>
                        <td>
                            <a href="<?php echo ADMIN_URL; ?>/coupons.php?action=delete&id=<?php echo $c['id']; ?>" onclick="return confirm('Delete coupon?')" class="btn btn-sm" style="background: #FEE2E2; color: #B91C1C;">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
