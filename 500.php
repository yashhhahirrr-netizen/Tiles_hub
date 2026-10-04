<?php
// 500.php
require_once __DIR__ . '/includes/header.php';
?>
<div class="container section-padding" style="text-align: center;">
    <h1 class="font-heading" style="font-size: 5rem; color: var(--color-warning);">500</h1>
    <h2 class="font-heading" style="margin-bottom: 16px;">Server System Error</h2>
    <p style="color: var(--color-text-muted); margin-bottom: 24px;">An internal server error occurred while processing your request. Technical logs have been updated.</p>
    <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-primary">Return to Home</a>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
