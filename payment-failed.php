<?php
// payment-failed.php
require_once __DIR__ . '/includes/header.php';
?>
<div class="container section-padding" style="text-align: center; max-width: 600px; margin: 0 auto;">
    <div style="width: 70px; height: 70px; background: #FEE2E2; color: #B91C1C; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; margin: 0 auto 20px;">✕</div>
    <h1 class="font-heading" style="margin-bottom: 16px;">Payment Unsuccessful</h1>
    <p style="color: var(--color-text-muted); margin-bottom: 24px;">Your online payment attempt could not be processed by the gateway. Your order items remain safely in your cart.</p>
    <div style="display: flex; gap: 16px; justify-content: center;">
        <a href="<?php echo BASE_URL; ?>/checkout.php" class="btn btn-primary">Retry Checkout</a>
        <a href="<?php echo BASE_URL; ?>/cart.php" class="btn btn-outline">Review Cart</a>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
