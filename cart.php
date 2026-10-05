<?php
// cart.php
// Tile Shopping Cart & Box Coverage Summary

require_once __DIR__ . '/config/app.php';

// Redirect to login if not authenticated, then come back to cart
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = BASE_URL . '/cart.php';
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';

$couponCode = trim($_GET['coupon'] ?? $_SESSION['coupon_code'] ?? '');
if (!empty($couponCode)) {
    $_SESSION['coupon_code'] = $couponCode;
}
$totals = getCartTotals($_SESSION['coupon_code'] ?? null);
?>

<div class="container section-padding">
    <div style="margin-bottom: 32px;">
        <span class="eyebrow">Shopping Summary</span>
        <h1 class="font-heading">Your Tile Cart</h1>
    </div>

    <?php if (empty($totals['items'])): ?>
        <div style="text-align: center; padding: 60px 20px; background: var(--color-card); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <h3 class="font-heading" style="margin-bottom: 12px;">Your Cart is Empty</h3>
            <p style="color: var(--color-text-muted); margin-bottom: 24px;">Browse our vitrified slabs and porcelain tiles to add box quantities to your order.</p>
            <a href="<?php echo BASE_URL; ?>/shop.php" class="btn btn-primary">Explore Collections</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 1fr 360px; gap: 40px;">
            <!-- Cart Items Table -->
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Tile Details</th>
                            <th>Box Price</th>
                            <th>Boxes</th>
                            <th>Total Coverage</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($totals['items'] as $item): 
                            $effectiveSqFtPrice = (!empty($item['discount_price']) && $item['discount_price'] > 0) ? $item['discount_price'] : $item['price_per_sqft'];
                            $boxPrice = getBoxPrice($effectiveSqFtPrice, $item['coverage_per_box']);
                            $itemTotal = $boxPrice * $item['boxes'];
                        ?>
                            <tr id="cart-row-<?php echo $item['item_id']; ?>">
                                <td style="display: flex; gap: 16px; align-items: center;">
                                    <img src="<?php echo BASE_URL . '/' . htmlspecialchars($item['image'] ?? 'assets/images/products/carrara-white-1.jpg'); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--radius-sm);" onerror="this.src='https://images.unsplash.com/photo-1590381105924-c72589b9ef3f?auto=format&fit=crop&w=150&q=80'">
                                    <div>
                                        <strong style="display: block; font-size: 0.95rem;"><?php echo htmlspecialchars($item['name']); ?></strong>
                                        <span style="font-size: 0.78rem; color: var(--color-text-muted);">SKU: <?php echo htmlspecialchars($item['sku']); ?> | Size: <?php echo htmlspecialchars($item['size']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php echo formatPrice($boxPrice); ?>
                                    <div style="font-size: 0.75rem; color: var(--color-text-light);">(<?php echo formatPrice($effectiveSqFtPrice); ?>/sq.ft)</div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; border: 1px solid var(--color-border); border-radius: var(--radius-sm); width: fit-content; background: #FFF;">
                                        <button class="js-update-cart-qty" data-item-id="<?php echo $item['item_id']; ?>" data-action="minus" style="padding: 4px 10px; border: none; background: none; cursor: pointer;">-</button>
                                        <span id="qty-val-<?php echo $item['item_id']; ?>" style="padding: 0 8px; font-weight: 600; font-size: 0.9rem;"><?php echo $item['boxes']; ?></span>
                                        <button class="js-update-cart-qty" data-item-id="<?php echo $item['item_id']; ?>" data-action="plus" style="padding: 4px 10px; border: none; background: none; cursor: pointer;">+</button>
                                    </div>
                                </td>
                                <td><?php echo round($item['coverage_per_box'] * $item['boxes'], 2); ?> sq. ft.</td>
                                <td><strong id="item-total-<?php echo $item['item_id']; ?>"><?php echo formatPrice($itemTotal); ?></strong></td>
                                <td>
                                    <button class="icon-btn js-remove-cart-item" data-item-id="<?php echo $item['item_id']; ?>" style="color: var(--color-danger);" title="Remove">âœ•</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Order Summary Card -->
            <div style="background: var(--color-card); padding: 32px; border-radius: var(--radius-md); border: 1px solid var(--color-border); height: fit-content;">
                <h3 class="font-heading" style="margin-bottom: 20px;">Order Summary</h3>

                <!-- Coupon Form -->
                <form action="<?php echo BASE_URL; ?>/cart.php" method="GET" style="margin-bottom: 24px;">
                    <div style="display: flex; gap: 8px;">
                        <input type="text" name="coupon" class="form-control" placeholder="Coupon Code (e.g. LUXETILES10)" value="<?php echo htmlspecialchars($_SESSION['coupon_code'] ?? ''); ?>">
                        <button type="submit" class="btn btn-outline btn-sm">Apply</button>
                    </div>
                </form>

                <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.925rem; border-bottom: 1px solid var(--color-border); padding-bottom: 20px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Total Boxes:</span>
                        <strong><?php echo $totals['total_boxes']; ?> Boxes</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Total Coverage Area:</span>
                        <strong><?php echo $totals['total_sqft']; ?> sq. ft.</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Subtotal:</span>
                        <span><?php echo formatPrice($totals['subtotal']); ?></span>
                    </div>

                    <?php if ($totals['discount'] > 0): ?>
                        <div style="display: flex; justify-content: space-between; color: var(--color-success);">
                            <span>Discount (<?php echo htmlspecialchars($totals['applied_coupon']['code']); ?>):</span>
                            <span>-<?php echo formatPrice($totals['discount']); ?></span>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between;">
                        <span>GST (18%):</span>
                        <span><?php echo formatPrice($totals['tax']); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Heavy Freight Shipping:</span>
                        <span><?php echo $totals['shipping'] > 0 ? formatPrice($totals['shipping']) : '<span style="color: var(--color-success);">FREE</span>'; ?></span>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: 700; margin-bottom: 24px; color: var(--color-primary);">
                    <span>Grand Total:</span>
                    <span><?php echo formatPrice($totals['grand_total']); ?></span>
                </div>

                <a href="<?php echo BASE_URL; ?>/checkout.php" class="btn btn-primary btn-lg btn-block">Proceed to Checkout &rarr;</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.js-update-cart-qty').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const isPlus = this.dataset.action === 'plus';
            const valSpan = document.getElementById('qty-val-' + itemId);
            let currentVal = parseInt(valSpan.textContent) || 1;
            let newVal = isPlus ? currentVal + 1 : currentVal - 1;
            if (newVal < 1) return;

            fetch(window.BASE_URL + '/api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'update',
                    item_id: itemId,
                    boxes: newVal
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showToast(data.message || 'Error updating cart', 'error');
                }
            });
        });
    });

    document.querySelectorAll('.js-remove-cart-item').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            fetch(window.BASE_URL + '/api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'remove',
                    item_id: itemId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
