<?php
// sample-request.php
// Tile Sample Request System

require_once __DIR__ . '/includes/header.php';
$db = getDBConnection();

$selectedProductId = (int)($_GET['product_id'] ?? 0);
$products = $db->query("SELECT id, name, sku, size, finish FROM products WHERE status = 'active' ORDER BY name ASC")->fetchAll();
$currentUser = getLoggedInUser();
?>

<div class="container section-padding">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 48px;">
        <span class="eyebrow">Tactile Specimen Box</span>
        <h1 class="font-heading">Order Cut Tile Samples</h1>
        <p class="subtitle">Experience tile glaze reflections, tactile surface textures, and color variations in your home light conditions before placing your full order.</p>
    </div>

    <div style="max-width: 700px; margin: 0 auto; background: var(--color-card); padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--color-border); box-shadow: var(--shadow-md);">
        <form id="sample-request-form">
            <div class="form-group">
                <label class="form-label">Select Tile Surface Specimen</label>
                <select name="product_id" class="form-control" required>
                    <option value="">-- Select a Tile Surface --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo $selectedProductId === $p['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['name']); ?> (<?php echo htmlspecialchars($p['size']); ?> — <?php echo htmlspecialchars($p['finish']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="customer_name" class="form-control" value="<?php echo htmlspecialchars($currentUser['full_name'] ?? ''); ?>" placeholder="e.g. Rahul Sharma" required>
            </div>

            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>" placeholder="e.g. 9876543210" required>
            </div>

            <div class="form-group">
                <label class="form-label">Delivery Street Address</label>
                <textarea name="address" class="form-control" rows="3" placeholder="Plot No, House Name, Street / Sector..." required></textarea>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">State</label>
                    <input type="text" name="state" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Pincode</label>
                    <input type="text" name="pincode" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top: 16px;">Submit Sample Request</button>
        </form>
    </div>
</div>

<script>
document.getElementById('sample-request-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch(window.BASE_URL + '/api/sample-request.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            this.reset();
        } else {
            showToast(data.message || 'Error submitting request', 'error');
        }
    })
    .catch(() => showToast('Network connection issue', 'error'));
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
