<?php
// admin/settings.php
// System Store Configuration Settings

require_once __DIR__ . '/admin-header.php';
$db = getDBConnection();

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $key => $val) {
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, trim($val), trim($val)]);
    }
    $message = "Store configuration settings updated!";
}

// Fetch all settings
$settingsRaw = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="admin-topbar">
    <div>
        <h1 class="font-heading" style="font-size: 2rem;">Store Settings</h1>
        <p style="color: var(--admin-text-muted); font-size: 0.9rem;">Configure global store parameters, GST percentage, and shipping rates.</p>
    </div>
</div>

<?php if ($message): ?>
    <div style="background: #DCFCE7; color: #15803D; padding: 14px; margin-bottom: 24px; border-radius: 4px;">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div style="background: #FFF; padding: 32px; border-radius: 8px; border: 1px solid var(--admin-border); max-width: 650px;">
    <form action="<?php echo ADMIN_URL; ?>/settings.php" method="POST">
        <div class="form-group">
            <label class="form-label">Store Brand Name</label>
            <input type="text" name="settings[site_name]" class="form-control" value="<?php echo htmlspecialchars($settingsRaw['site_name'] ?? 'TilePoint Premium Tiles & Surfaces'); ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Concierge Contact Email</label>
            <input type="email" name="settings[site_email]" class="form-control" value="<?php echo htmlspecialchars($settingsRaw['site_email'] ?? 'contact@tilepoint.com'); ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Concierge Phone Number</label>
            <input type="text" name="settings[site_phone]" class="form-control" value="<?php echo htmlspecialchars($settingsRaw['site_phone'] ?? '+91 98765 43210'); ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Studio Physical Address</label>
            <textarea name="settings[store_address]" class="form-control" rows="3"><?php echo htmlspecialchars($settingsRaw['store_address'] ?? 'TilePoint Studio, 100 Feet Road, Indiranagar, Bengaluru'); ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">GST Tax Rate (%)</label>
                <input type="number" name="settings[gst_percentage]" class="form-control" value="<?php echo htmlspecialchars($settingsRaw['gst_percentage'] ?? '18'); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Flat Freight Shipping (₹)</label>
                <input type="number" step="0.01" name="settings[flat_shipping_rate]" class="form-control" value="<?php echo htmlspecialchars($settingsRaw['flat_shipping_rate'] ?? '499.00'); ?>">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <label class="form-label">Free Shipping Threshold Amount (₹)</label>
            <input type="number" step="0.01" name="settings[free_shipping_threshold]" class="form-control" value="<?php echo htmlspecialchars($settingsRaw['free_shipping_threshold'] ?? '25000.00'); ?>">
        </div>

        <button type="submit" class="btn btn-accent btn-lg">Save Settings</button>
    </form>
</div>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
